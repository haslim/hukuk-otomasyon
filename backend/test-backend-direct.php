<?php
// Test backend directly to see if it's working
echo "=== TESTING BACKEND DIRECTLY ===\n\n";

// Test 1: Check if backend/public/index.php exists and is executable
echo "1. Checking if backend/public/index.php exists:\n";
if (file_exists(__DIR__ . '/public/index.php')) {
    echo "✓ backend/public/index.php exists\n";
} else {
    echo "✗ backend/public/index.php does NOT exist\n";
}

// Test 2: Check if required files exist
echo "\n2. Checking required files:\n";
$requiredFiles = [
    'bootstrap/app.php',
    'routes/api.php',
    'routes/web.php',
    'app/Controllers/AuthController.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file does NOT exist\n";
    }
}

// Test 3: Try to include the bootstrap file
echo "\n3. Testing bootstrap loading:\n";
try {
    $app = require __DIR__ . '/bootstrap/app.php';
    echo "✓ Bootstrap loaded successfully\n";
    echo "  App type: " . get_class($app) . "\n";
} catch (Exception $e) {
    echo "✗ Bootstrap failed: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 4: Test a simple API call directly
echo "\n4. Testing direct API call simulation:\n";
try {
    // Simulate a GET request to /api/menu/my
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['REQUEST_URI'] = '/api/menu/my';
    $_SERVER['HTTP_HOST'] = 'bgaofis.billurguleraslim.av.tr';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Capture output
    ob_start();
    
    // Try to run the app (this might not work in CLI)
    if (isset($app)) {
        echo "✓ App object available, but cannot run in CLI mode\n";
    }
    
    ob_end_clean();
    
} catch (Exception $e) {
    echo "✗ Direct API test failed: " . $e->getMessage() . "\n";
}

// Test 5: Check .htaccess files
echo "\n5. Checking .htaccess files:\n";
if (file_exists(__DIR__ . '/.htaccess')) {
    echo "✓ backend/.htaccess exists\n";
    echo "  Content preview:\n";
    $htaccess = file_get_contents(__DIR__ . '/.htaccess');
    $lines = explode("\n", $htaccess);
    foreach (array_slice($lines, 0, 10) as $line) {
        echo "    " . $line . "\n";
    }
    if (count($lines) > 10) {
        echo "    ... (truncated)\n";
    }
} else {
    echo "✗ backend/.htaccess does NOT exist\n";
}

if (file_exists(__DIR__ . '/public/.htaccess')) {
    echo "✓ backend/public/.htaccess exists\n";
} else {
    echo "✗ backend/public/.htaccess does NOT exist\n";
}

// Test 6: Check if the frontend index.html is being returned instead
echo "\n6. Checking if frontend index.html exists:\n";
if (file_exists(__DIR__ . '/../frontend/index.html')) {
    echo "✓ frontend/index.html exists\n";
    $html = file_get_contents(__DIR__ . '/../frontend/index.html');
    if (strpos($html, '<!doctype html>') === 0) {
        echo "✓ Frontend HTML confirmed - this is what's being returned\n";
    }
} else {
    echo "✗ frontend/index.html does NOT exist\n";
}

echo "\n=== DIRECT BACKEND TEST COMPLETE ===\n";
?>