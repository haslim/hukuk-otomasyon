<?php

/**
 * Production Deployment Test
 * This script checks if the deployment has been applied to production
 */

echo "=== Production Deployment Check ===\n";

// Test 1: Check if the route file has been updated
$routeFile = '/home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend/routes/api.php';
if (file_exists($routeFile)) {
    $routeContent = file_get_contents($routeFile);
    
    // Check if statistics route comes before {id} route
    $statsPos = strpos($routeContent, "get('/statistics'");
    $idPos = strpos($routeContent, "get('/{id}'");
    
    if ($statsPos !== false && $idPos !== false) {
        if ($statsPos < $idPos) {
            echo "✓ Route order is CORRECT: /statistics comes before /{id}\n";
        } else {
            echo "✗ Route order is WRONG: /{id} comes before /statistics\n";
        }
    } else {
        echo "? Could not find both routes in the file\n";
    }
    
    echo "Route file size: " . strlen($routeContent) . " bytes\n";
    echo "Last modified: " . date('Y-m-d H:i:s', filemtime($routeFile)) . "\n";
} else {
    echo "✗ Route file not found at: $routeFile\n";
}

echo "\n=== Current Route Content (Arbitration Section) ===\n";
if (isset($routeContent)) {
    // Extract arbitration section
    preg_match('/\/\/ Arabuluculuk routes.*?->add\(new AuditLogMiddleware\(\'arbitration\'\)\);/s', $routeContent, $matches);
    if ($matches) {
        echo $matches[0] . "\n";
    } else {
        echo "Could not extract arbitration section\n";
    }
}

echo "\n=== Deployment Info ===\n";
$deploymentFile = '/home/haslim/public_html/bgaofis.billurguleraslim.av.tr/deployment-info.json';
if (file_exists($deploymentFile)) {
    $deploymentInfo = json_decode(file_get_contents($deploymentFile), true);
    echo "Last deployment: " . ($deploymentInfo['timestamp'] ?? 'Unknown') . "\n";
    echo "Commit: " . ($deploymentInfo['commit'] ?? 'Unknown') . "\n";
    echo "Status: " . ($deploymentInfo['status'] ?? 'Unknown') . "\n";
} else {
    echo "✗ No deployment info file found\n";
}

echo "\n=== Test Complete ===\n";
