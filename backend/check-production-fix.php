<?php

/**
 * Production Check Script
 * This will be uploaded to production to check deployment status
 */

echo "=== Production Route Fix Check ===\n";

// Load routes file like the application does
$routeFile = __DIR__ . '/routes/api.php';
if (!file_exists($routeFile)) {
    echo "✗ Routes file not found\n";
    exit(1);
}

$routeContent = file_get_contents($routeFile);

// Find arbitration section
preg_match('/\$protected->group\(\'\/arbitration\', function \(Group \$arbitration\) \{(.*?)\}\)->add\(new AuditLogMiddleware\(\'arbitration\'\)\);/s', $routeContent, $matches);

if (!isset($matches[1])) {
    echo "✗ Could not find arbitration route group\n";
    exit(1);
}

$arbitrationRoutes = $matches[1];
echo "Found arbitration route group:\n";
echo $arbitrationRoutes . "\n\n";

// Check route order
$statisticsLine = strpos($arbitrationRoutes, "get('/statistics'");
$idLine = strpos($arbitrationRoutes, "get('/{id}'");

if ($statisticsLine === false) {
    echo "✗ Statistics route not found\n";
    exit(1);
}

if ($idLine === false) {
    echo "✗ ID route not found\n";
    exit(1);
}

if ($statisticsLine < $idLine) {
    echo "✓ CORRECT: /statistics route comes before /{id} route\n";
    echo "✓ Route shadowing issue should be FIXED\n";
} else {
    echo "✗ PROBLEM: /{id} route comes before /statistics route\n";
    echo "✗ Route shadowing issue still EXISTS\n";
}

echo "\n=== Route Analysis Complete ===\n";
