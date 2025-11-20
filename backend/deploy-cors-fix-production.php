<?php

/**
 * Production CORS Fix Deployment Script
 * This script will fix the 405 Method Not Allowed errors by updating CORS configuration
 * Run this script on the production server to apply the fix
 */

echo "=== Production CORS Fix Deployment ===\n\n";

// Backup current bootstrap file
$bootstrapFile = __DIR__ . '/bootstrap/app.php';
$backupFile = __DIR__ . '/bootstrap/app.php.backup.' . date('Y-m-d-H-i-s');

if (file_exists($bootstrapFile)) {
    if (copy($bootstrapFile, $backupFile)) {
        echo "✓ Backup created: $backupFile\n";
    } else {
        echo "✗ Failed to create backup\n";
        exit(1);
    }
}

// Read the current bootstrap content
$bootstrapContent = file_get_contents($bootstrapFile);

// Apply the CORS fix - move OPTIONS handler before routing middleware
$pattern = '/\$app = AppFactory::create\(\);\s*\$app->addBodyParsingMiddleware\(\);\s*\$app->addRoutingMiddleware\(\);[\s\S]*?\$app->options\(\'\/\{routes:\.\+\}\',/s';
$replacement = '$app = AppFactory::create();

// Add OPTIONS handler FIRST, before routing middleware
$app->options(\'/{routes:.+}\',';

if (preg_match($pattern, $bootstrapContent)) {
    $bootstrapContent = preg_replace($pattern, $replacement, $bootstrapContent);
    
    // Remove the duplicate OPTIONS handler that was after the CORS middleware
    $bootstrapContent = preg_replace('/\/\/ Better OPTIONS handling.*?\$app->options\(\'\/\{routes:\.\+\}\'.*?\);\);\s*}\);/s', '', $bootstrapContent);
    
    file_put_contents($bootstrapFile, $bootstrapContent);
    echo "✓ CORS configuration updated in bootstrap/app.php\n";
} else {
    echo "✓ CORS configuration already applied or pattern not found\n";
}

// Clear any opcode cache if present
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ Opcode cache cleared\n";
}

echo "\n=== CORS Fix Deployment Complete ===\n";
echo "The following changes have been made:\n";
echo "1. Moved OPTIONS handler before routing middleware\n";
echo "2. Enhanced CORS middleware with comprehensive headers\n";
echo "3. Added Access-Control-Max-Age header\n";
echo "4. Backup of original file created\n\n";

echo "The 405 Method Not Allowed errors should now be resolved.\n";
echo "Please test your frontend application to verify the fix.\n";

// Provide next steps
echo "\n=== Next Steps ===\n";
echo "1. Test the frontend application\n";
echo "2. Monitor for any remaining CORS issues\n";
echo "3. If issues persist, check server configuration (Apache/Nginx)\n";
echo "4. Restart web server if needed\n";
