<?php
/**
 * BGAofis Hukuk Otomasyon - Webhook Handler
 * This script handles GitHub webhooks for automatic deployment
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log file for webhook events
$logFile = __DIR__ . '/webhook.log';

// Function to log messages
function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Function to send response
function sendResponse($statusCode, $message) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode(['message' => $message]);
    logMessage($message);
    exit;
}

// Verify secret token
function verifySignature($payload, $signature, $secret) {
    $hash = 'sha256=' . hash_hmac('sha256', $payload, $secret);
    return hash_equals($hash, $signature);
}

// Main webhook handling
try {
    logMessage('Webhook received');
    
    // Get the signature from headers
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '';
    
    // Get the payload
    $payload = file_get_contents('php://input');
    
    // Get secret from environment or config
    $secret = $_ENV['WEBHOOK_SECRET'] ?? 'your-default-secret-key';
    
    // Verify the signature
    if (!verifySignature($payload, $signature, $secret)) {
        logMessage('Invalid signature - webhook rejected');
        sendResponse(403, 'Invalid signature');
    }
    
    // Parse the payload
    $data = json_decode($payload, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage('Invalid JSON payload: ' . json_last_error_msg());
        sendResponse(400, 'Invalid JSON payload');
    }
    
    // Get repository and branch information
    $repository = $data['repository']['name'] ?? 'unknown';
    $branch = str_replace('refs/heads/', '', $data['ref'] ?? 'unknown');
    $commit = $data['after'] ?? 'unknown';
    
    logMessage("Push to {$repository}/{$branch} (commit: {$commit})");
    
    // Check if this is a push to main/master branch
    if (!in_array($branch, ['main', 'master'])) {
        logMessage("Ignoring push to branch: {$branch}");
        sendResponse(200, 'Push to non-main branch ignored');
    }
    
    // Check if this is the correct repository
    $expectedRepo = $_ENV['REPO_NAME'] ?? 'hukuk-otomasyon';
    if ($repository !== $expectedRepo) {
        logMessage("Ignoring push to repository: {$repository}");
        sendResponse(200, 'Push to different repository ignored');
    }
    
    // Start deployment process
    logMessage('Starting deployment process...');
    
    // Change to project directory
    $projectDir = __DIR__ . '/..';
    chdir($projectDir);
    
    // Pull latest changes
    $output = [];
    $returnCode = 0;
    exec('git pull origin ' . $branch . ' 2>&1', $output, $returnCode);
    
    if ($returnCode !== 0) {
        logMessage('Git pull failed: ' . implode("\n", $output));
        sendResponse(500, 'Git pull failed');
    }
    
    logMessage('Git pull successful');
    
    // Deploy backend
    logMessage('Deploying backend...');
    chdir($projectDir . '/backend');
    
    // Install composer dependencies if needed
    if (!file_exists('vendor') || !file_exists('vendor/autoload.php')) {
        exec('composer install --no-dev --optimize-autoloader 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            logMessage('Composer install failed: ' . implode("\n", $output));
            sendResponse(500, 'Composer install failed');
        }
        logMessage('Composer install successful');
    }
    
    // Run database migrations
    exec('php database/migrate.php 2>&1', $output, $returnCode);
    if ($returnCode !== 0) {
        logMessage('Database migrations failed: ' . implode("\n", $output));
        sendResponse(500, 'Database migrations failed');
    }
    logMessage('Database migrations successful');
    
    // Deploy frontend
    logMessage('Deploying frontend...');
    chdir($projectDir . '/frontend');
    
    // Install npm dependencies if needed
    if (!file_exists('node_modules')) {
        exec('npm install 2>&1', $output, $returnCode);
        if ($returnCode !== 0) {
            logMessage('NPM install failed: ' . implode("\n", $output));
            sendResponse(500, 'NPM install failed');
        }
        logMessage('NPM install successful');
    }
    
    // Build frontend
    exec('npm run build 2>&1', $output, $returnCode);
    if ($returnCode !== 0) {
        logMessage('Frontend build failed: ' . implode("\n", $output));
        sendResponse(500, 'Frontend build failed');
    }
    logMessage('Frontend build successful');
    
    // Copy built files to web root
    $webRoot = $_ENV['WEB_ROOT'] ?? __DIR__ . '/../../public_html';
    if (!is_dir($webRoot)) {
        mkdir($webRoot, 0755, true);
    }
    
    exec('cp -r dist/* ' . $webRoot . '/ 2>&1', $output, $returnCode);
    if ($returnCode !== 0) {
        logMessage('Copy to web root failed: ' . implode("\n", $output));
        sendResponse(500, 'Copy to web root failed');
    }
    logMessage('Files copied to web root successfully');
    
    // Create deployment info
    $deploymentInfo = [
        'timestamp' => date('c'),
        'commit' => $commit,
        'branch' => $branch,
        'repository' => $repository,
        'status' => 'success'
    ];
    
    file_put_contents($webRoot . '/deployment-info.json', json_encode($deploymentInfo, JSON_PRETTY_PRINT));
    
    logMessage('Deployment completed successfully');
    sendResponse(200, 'Deployment completed successfully');
    
} catch (Exception $e) {
    logMessage('Exception occurred: ' . $e->getMessage());
    sendResponse(500, 'Deployment failed: ' . $e->getMessage());
}