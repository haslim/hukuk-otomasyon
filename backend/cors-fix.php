<?php

// CORS Fix for Production Server
// This script ensures proper CORS handling for all HTTP methods

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// Add this to bootstrap/app.php before $app->addErrorMiddleware()

// Enhanced CORS handling
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    
    // Get allowed origin from environment or allow all for development
    $allowedOrigin = $_ENV['CORS_ORIGIN'] ?? '*';
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Cache-Control, X-File-Name')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Max-Age', '86400'); // 24 hours
});

// Better OPTIONS handling - place this AFTER the CORS middleware above
$app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    return $response
        ->withStatus(200)
        ->withHeader('Access-Control-Allow-Origin', $_ENV['CORS_ORIGIN'] ?? '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, Cache-Control, X-File-Name')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true')
        ->withHeader('Access-Control-Max-Age', '86400')
        ->withHeader('Content-Length', '0');
});

echo "CORS fix generated. Please replace the CORS middleware in bootstrap/app.php with the enhanced version above.\n";
echo "The key changes are:\n";
echo "1. More comprehensive header list\n";
echo "2. Proper OPTIONS response with correct status and headers\n";
echo "3. Access-Control-Max-Age to reduce preflight requests\n";
