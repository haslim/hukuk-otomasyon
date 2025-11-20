<?php

// Simple API route test script
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$envPath = __DIR__;
if (file_exists($envPath . '/.env')) {
    Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
}

// Create app
$app = require __DIR__ . '/bootstrap/app.php';

// Load routes
(require __DIR__ . '/routes/web.php')($app);
(require __DIR__ . '/routes/api.php')($app);

// Get route collector
$routeCollector = $app->getRouteCollector();

echo "=== API Route Test ===\n\n";

// Get all routes
$routes = $routeCollector->getRoutes();

$apiRoutes = [];
foreach ($routes as $route) {
    $pattern = $route->getPattern();
    if (strpos($pattern, '/api') === 0) {
        $methods = $route->getMethods();
        $apiRoutes[] = [
            'pattern' => $pattern,
            'methods' => implode(', ', $methods),
            'callable' => $route->getCallable()
        ];
    }
}

echo "Found " . count($apiRoutes) . " API routes:\n\n";

foreach ($apiRoutes as $route) {
    echo "Path: {$route['pattern']}\n";
    echo "Methods: {$route['methods']}\n";
    echo "Handler: " . (is_string($route['callable']) ? $route['callable'] : 'Closure') . "\n";
    echo "---\n";
}

// Test specific routes that were failing
$failingRoutes = ['/api/dashboard', '/api/notifications', '/api/menu/my'];

echo "\n=== Testing Specific Failing Routes ===\n";

foreach ($failingRoutes as $testRoute) {
    $found = false;
    foreach ($apiRoutes as $route) {
        if ($route['pattern'] === $testRoute || 
            (strpos($route['pattern'], '{') !== false && strpos($testRoute, str_replace('{', '', explode('}', $route['pattern'])[0])) === 0)) {
            echo "✓ Route found: $testRoute (Methods: {$route['methods']})\n";
            $found = true;
            break;
        }
    }
    if (!$found) {
        echo "✗ Route NOT found: $testRoute\n";
    }
}

echo "\n=== Test Complete ===\n";
