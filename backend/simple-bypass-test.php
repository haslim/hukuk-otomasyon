<?php

/**
 * Simple Bypass Test
 * Direct test of bypass functionality
 */

echo "=== SIMPLE BYPASS TEST ===\n\n";

// Test database connection directly
echo "STEP 1: Testing Database Connection...\n";

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=haslim_bgofis;charset=utf8mb4",
        "haslim_bgofis", "Fener1907****",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "✅ Database connection: SUCCESS\n";
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test each bypass file by simulating API calls
echo "\nSTEP 2: Testing Bypass Files...\n";

$bypasses = [
    'menu-bypass.php' => '/api/menu/my',
    'dashboard-bypass.php' => '/api/dashboard', 
    'notifications-bypass.php' => '/api/notifications',
    'working-login.php' => '/api/auth/login'
];

foreach ($bypasses as $file => $route) {
    echo "Testing: $file (route: $route)\n";
    
    if (!file_exists(__DIR__ . '/' . $file)) {
        echo "❌ File not found\n";
        continue;
    }
    
    // Simulate GET request
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
    
    ob_start();
    include __DIR__ . '/' . $file;
    $output = ob_get_clean();
    
    $data = json_decode($output, true);
    if ($data && isset($data['success'])) {
        echo "✅ $file: " . ($data['success'] ? 'Working' : 'Error: ' . ($data['message'] ?? 'Unknown')) . "\n";
    } else {
        echo "⚠️  $file: Unexpected output\n";
    }
}

// Test login bypass with POST data
echo "\nSTEP 3: Testing Login with POST Data...\n";

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['email' => 'alihaydaraslim@gmail.com', 'password' => 'test123456'];

ob_start();
include __DIR__ . '/working-login.php';
$loginOutput = ob_get_clean();

$loginData = json_decode($loginOutput, true);
if ($loginData && isset($loginData['success'])) {
    if ($loginData['success']) {
        echo "✅ Login: SUCCESS\n";
        echo "✅ User: " . ($loginData['user']['name'] ?? 'Unknown') . "\n";
    } else {
        echo "❌ Login: Failed - " . ($loginData['message'] ?? 'Unknown') . "\n";
    }
} else {
    echo "⚠️  Login: Unexpected output\n";
}

echo "\n=== SIMPLE BYPASS TEST COMPLETE ===\n";
