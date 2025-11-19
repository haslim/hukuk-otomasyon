<?php

require_once 'vendor/autoload.php';

// Bootstrap the application
$app = require_once 'bootstrap/app.php';

// Load routes like public/index.php does
(require_once 'routes/web.php')($app);
(require_once 'routes/api.php')($app);

// Test routes
echo "=== Route Test ===\n";

try {
    // Test route collector
    $routeCollector = $app->getRouteCollector();
    $routes = $routeCollector->getRoutes();
    
    echo "Total routes: " . count($routes) . "\n";
    
    // Check arbitration routes specifically
    foreach ($routes as $route) {
        $pattern = $route->getPattern();
        if (strpos($pattern, 'arbitration') !== false) {
            echo "Found arbitration route: " . $pattern . "\n";
            echo "Methods: " . implode(', ', $route->getMethods()) . "\n";
        }
    }
    
    echo "✓ Routes loaded successfully\n";
    
} catch (Exception $e) {
    echo "✗ Route error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== Complete ===\n";
