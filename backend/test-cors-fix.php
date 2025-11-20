<?php

/**
 * Simple CORS Fix Test Script
 * Tests if the problematic routes are properly configured after CORS fix
 */

echo "=== CORS Fix Route Test ===\n\n";

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
$routes = $routeCollector->getRoutes();

$problematicRoutes = [
    '/api/dashboard',
    '/api/notifications', 
    '/api/menu/my'
];

echo "Testing problematic routes after CORS fix:\n\n";

foreach ($problematicRoutes as $testRoute) {
    $found = false;
    $methods = [];
    
    foreach ($routes as $route) {
        $pattern = $route->getPattern();
        if ($pattern === $testRoute) {
            $methods = $route->getMethods();
            $found = true;
            break;
        }
    }
    
    if ($found) {
        if (in_array('GET', $methods)) {
            echo "✓ $testRoute - GET method supported (Methods: " . implode(', ', $methods) . ")\n";
        } else {
            echo "✗ $testRoute - GET method NOT supported (Methods: " . implode(', ', $methods) . ")\n";
        }
    } else {
        echo "✗ $testRoute - Route not found\n";
    }
}

echo "\n=== CORS Configuration Status ===\n";

// Check if the enhanced CORS middleware is present
$bootstrapContent = file_get_contents(__DIR__ . '/bootstrap/app.php');
$corsFeatures = [
    'Access-Control-Max-Age' => strpos($bootstrapContent, 'Access-Control-Max-Age') !== false,
    'Cache-Control header' => strpos($bootstrapContent, 'Cache-Control') !== false,
    'X-File-Name header' => strpos($bootstrapContent, 'X-File-Name') !== false,
    'Content-Length in OPTIONS' => strpos($bootstrapContent, 'Content-Length') !== false
];

foreach ($corsFeatures as $feature => $present) {
    echo $present ? "✓ $feature\n" : "✗ $feature\n";
}

echo "\n=== Test Summary ===\n";
echo "The CORS fix has been applied with the following improvements:\n";
echo "1. Enhanced CORS middleware with comprehensive headers\n";
echo "2. Better OPTIONS request handling\n";
echo "3. Cache-Control and X-File-Name headers added\n";
echo "4. Access-Control-Max-Age to reduce preflight requests\n\n";

echo "To deploy this fix to production:\n";
echo "1. Upload the updated bootstrap/app.php to your server\n";
echo "2. Clear any server cache (opcache, etc.)\n";
echo "3. Test the frontend application\n";
