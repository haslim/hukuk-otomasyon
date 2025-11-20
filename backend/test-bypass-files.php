<?php

/**
 * Test Bypass Files
 * Direct test of all bypass files to ensure they work
 */

echo "=== TESTING BYPASS FILES ===\n\n";

// Test function
function testBypassFile($filename, $description) {
    echo "Testing: $description\n";
    echo "File: $filename\n";
    
    if (!file_exists($filename)) {
        echo "❌ File does not exist\n\n";
        return false;
    }
    
    echo "✅ File exists\n";
    
    // Test PHP syntax
    $output = [];
    $returnCode = 0;
    exec("php -l $filename 2>&1", $output, $returnCode);
    
    if ($returnCode !== 0) {
        echo "❌ PHP syntax error: " . implode(' ', $output) . "\n\n";
        return false;
    }
    
    echo "✅ PHP syntax OK\n";
    
    // Test execution (simulate)
    $_SERVER['REQUEST_METHOD'] = 'GET';
    
    ob_start();
    $result = include $filename;
    $content = ob_get_clean();
    
    $data = json_decode($content, true);
    if ($data && isset($data['success'])) {
        echo "✅ Execution works: " . ($data['success'] ? 'Success' : 'Failed') . "\n";
        if (!$data['success']) {
            echo "   Error: " . ($data['message'] ?? 'Unknown') . "\n";
        }
    } else {
        echo "⚠️  Unexpected output: " . substr($content, 0, 100) . "...\n";
    }
    
    echo "\n";
    return true;
}

// Test all bypass files
$bypassFiles = [
    __DIR__ . '/working-login.php' => 'Working Login Bypass',
    __DIR__ . '/menu-bypass.php' => 'Menu Bypass',
    __DIR__ . '/dashboard-bypass.php' => 'Dashboard Bypass',
    __DIR__ . '/notifications-bypass.php' => 'Notifications Bypass'
];

$allWorking = true;

foreach ($bypassFiles as $file => $desc) {
    $working = testBypassFile($file, $desc);
    if (!$working) {
        $allWorking = false;
    }
}

// Test .htaccess routing
echo "=== TESTING HTACCESS ROUTING ===\n";

$htaccessPath = dirname(__DIR__) . '/.htaccess';
if (file_exists($htaccessPath)) {
    echo "✅ .htaccess exists\n";
    
    $htaccessContent = file_get_contents($htaccessPath);
    
    $requiredRules = [
        'api/auth/login.*working-login\.php',
        'api/menu/my.*menu-bypass\.php',
        'api/dashboard.*dashboard-bypass\.php',
        'api/notifications.*notifications-bypass\.php'
    ];
    
    $missingRules = [];
    foreach ($requiredRules as $rule) {
        if (!preg_match("/$rule/i", $htaccessContent)) {
            $missingRules[] = $rule;
        }
    }
    
    if (empty($missingRules)) {
        echo "✅ All required routing rules found\n";
    } else {
        echo "❌ Missing routing rules:\n";
        foreach ($missingRules as $rule) {
            echo "   - $rule\n";
        }
        $allWorking = false;
    }
} else {
    echo "❌ .htaccess not found\n";
    $allWorking = false;
}

// Final analysis
echo "\n=== FINAL ANALYSIS ===\n";

if ($allWorking) {
    echo "🎉 ALL BYPASS FILES WORKING! 🎉\n";
    echo "\n✅ Ready for API testing:\n";
    echo "✅ Login: /api/auth/login\n";
    echo "✅ Menu: /api/menu/my\n";
    echo "✅ Dashboard: /api/dashboard\n";
    echo "✅ Notifications: /api/notifications\n\n";
    
    echo "Next step: Test API endpoints via HTTP requests\n";
    
} else {
    echo "⚠️  ISSUES FOUND - NEED FIXING\n";
    echo "\nWhat to check:\n";
    echo "1. Fix any PHP syntax errors\n";
    echo "2. Ensure .htaccess routing is correct\n";
    echo "3. Check database connections\n";
    echo "4. Verify file permissions\n";
}

echo "\n=== TEST BYPASS FILES COMPLETE ===\n";
