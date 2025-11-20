<?php
// Complete test of the backend including route loading
echo "=== TESTING COMPLETE BACKEND ===\n\n";

// Change to public directory to simulate web server
$originalDir = getcwd();
chdir(__DIR__ . '/public');

try {
    echo "1. Setting up server environment:\n";
    
    // Set up comprehensive server variables
    $_SERVER = [
        'REQUEST_METHOD' => 'GET',
        'REQUEST_URI' => '/',
        'HTTP_HOST' => 'bgaofis.billurguleraslim.av.tr',
        'SERVER_NAME' => 'bgaofis.billurguleraslim.av.tr',
        'HTTPS' => 'on',
        'SERVER_PORT' => '443',
        'REQUEST_SCHEME' => 'https',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Test Script)',
        'REMOTE_ADDR' => '127.0.0.1',
        'SCRIPT_NAME' => '/index.php',
        'SCRIPT_FILENAME' => __DIR__ . '/index.php',
        'PHP_SELF' => '/index.php',
        'DOCUMENT_ROOT' => __DIR__,
        'SERVER_PROTOCOL' => 'HTTP/1.1'
    ];
    
    echo "   ✓ Server environment configured\n";
    
    // Load the complete application
    echo "\n2. Loading complete application:\n";
    
    // Include the index.php file which loads routes
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    
    echo "   ✓ Application executed without fatal errors\n";
    
    // Analyze the output
    echo "\n3. Analyzing output:\n";
    echo "   Output length: " . strlen($output) . " bytes\n";
    
    if (strlen($output) > 0) {
        echo "   First 300 characters:\n";
        echo "   " . substr($output, 0, 300) . "...\n";
        
        // Check if it's valid JSON
        $json = json_decode($output, true);
        if ($json !== null) {
            echo "   ✓ Output is valid JSON\n";
            if (isset($json['status']) && $json['status'] === 'ok') {
                echo "   ✓ Root route is working correctly\n";
            }
        } else {
            echo "   ✗ Output is NOT valid JSON\n";
            echo "   JSON error: " . json_last_error_msg() . "\n";
            
            // Check for HTML
            if (strpos($output, '<!doctype html') === 0 || strpos($output, '<html') === 0) {
                echo "   ✗ Output is HTML instead of JSON\n";
            }
        }
    } else {
        echo "   ✗ No output received\n";
    }
    
    // Test API route
    echo "\n4. Testing API route (/api/menu/my):\n";
    $_SERVER['REQUEST_URI'] = '/api/menu/my';
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    
    echo "   Output length: " . strlen($output) . " bytes\n";
    if (strlen($output) > 0) {
        $json = json_decode($output, true);
        if ($json !== null) {
            echo "   ✓ API route returns valid JSON\n";
            if (isset($json['message']) && strpos($json['message'], 'Authorization') !== false) {
                echo "   ✓ Authentication middleware is working\n";
            }
        } else {
            echo "   ✗ API route does not return valid JSON\n";
            echo "   First 200 chars: " . substr($output, 0, 200) . "...\n";
        }
    }
    
    // Test auth login route
    echo "\n5. Testing auth login route (/api/auth/login):\n";
    $_SERVER['REQUEST_URI'] = '/api/auth/login';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Simulate POST data
    $postData = json_encode(['email' => 'test@test.com', 'password' => 'test']);
    
    // Use php://input for POST data
    $tempFile = tempnam(sys_get_temp_dir(), 'post_data');
    file_put_contents($tempFile, $postData);
    $_SERVER['CONTENT_LENGTH'] = strlen($postData);
    
    // Backup original php://input
    $backupInput = 'php://input';
    
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    
    echo "   Output length: " . strlen($output) . " bytes\n";
    if (strlen($output) > 0) {
        $json = json_decode($output, true);
        if ($json !== null) {
            echo "   ✓ Auth login route returns valid JSON\n";
            echo "   Response: " . substr($output, 0, 200) . "...\n";
        } else {
            echo "   ✗ Auth login route does not return valid JSON\n";
            echo "   First 200 chars: " . substr($output, 0, 200) . "...\n";
        }
    }
    
    // Clean up
    unlink($tempFile);
    
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "   ✗ Fatal Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
} finally {
    chdir($originalDir);
}

echo "\n=== COMPLETE BACKEND TEST FINISHED ===\n";
?>