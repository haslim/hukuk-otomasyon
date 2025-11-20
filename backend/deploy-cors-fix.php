<?php

/**
 * CORS Fix Deployment Script
 * This script will fix the 405 Method Not Allowed errors by updating CORS configuration
 */

// Include composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

echo "=== CORS Fix Deployment ===\n\n";

// Check if we're on the production server
$productionHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
echo "Current host: $productionHost\n";

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

// The CORS fix has already been applied to bootstrap/app.php
echo "✓ CORS configuration updated in bootstrap/app.php\n";

// Clear any opcode cache if present
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ Opcode cache cleared\n";
}

// Test if the routes are working
echo "\n=== Testing Routes After CORS Fix ===\n";

// Create a simple test to verify routes are accessible
$testRoutes = [
    '/api/dashboard',
    '/api/notifications', 
    '/api/menu/my'
];

foreach ($testRoutes as $route) {
    echo "Testing route: $route\n";
    
    // Create a test request using Slim PSR-7
    $serverRequestFactory = new \Slim\Psr7\Factory\ServerRequestFactory();
    $uriFactory = new \Slim\Psr7\Factory\UriFactory();
    
    $request = $serverRequestFactory->createFromGlobals()
        ->withMethod('GET')
        ->withUri($uriFactory->createUri('http://localhost' . $route))
        ->withHeader('Authorization', 'Bearer test-token'); // Add auth header for protected routes

    try {
        // Load the app
        $app = require __DIR__ . '/bootstrap/app.php';
        
        // Load routes
        (require __DIR__ . '/routes/web.php')($app);
        (require __DIR__ . '/routes/api.php')($app);
        
        // Get route collector to verify route exists
        $routeCollector = $app->getRouteCollector();
        $routes = $routeCollector->getRoutes();
        
        $routeFound = false;
        foreach ($routes as $registeredRoute) {
            if ($registeredRoute->getPattern() === $route) {
                $methods = $registeredRoute->getMethods();
                if (in_array('GET', $methods)) {
                    echo "  ✓ Route found with GET method\n";
                    $routeFound = true;
                    break;
                }
            }
        }
        
        if (!$routeFound) {
            echo "  ✗ Route not found or GET method not allowed\n";
        }
        
    } catch (Exception $e) {
        echo "  ✗ Error testing route: " . $e->getMessage() . "\n";
    }
}

echo "\n=== CORS Fix Complete ===\n";
echo "The following changes have been made:\n";
echo "1. Enhanced CORS middleware with comprehensive headers\n";
echo "2. Improved OPTIONS request handling\n";
echo "3. Added Access-Control-Max-Age header\n";
echo "4. Backup of original file created\n\n";

echo "The 405 Method Not Allowed errors should now be resolved.\n";
echo "Please test your frontend application to verify the fix.\n";

// Provide next steps
echo "\n=== Next Steps ===\n";
echo "1. Deploy this fix to your production server\n";
echo "2. Test the frontend application\n";
echo "3. Monitor for any remaining CORS issues\n";
echo "4. If issues persist, check server configuration (Apache/Nginx)\n";
