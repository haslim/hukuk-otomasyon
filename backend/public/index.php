<?php

$app = require __DIR__ . '/../bootstrap/app.php';

// Web / root routes (health check, etc.)
(require __DIR__ . '/../routes/web.php')($app);

// API routes
(require __DIR__ . '/../routes/api.php')($app);

// Add our custom error handling before running
$app->add(function($request, $handler) {
    try {
        return $handler->handle($request);
    } catch (Exception $e) {
        // Log error
        error_log("Slim Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        
        // Return detailed error response
        $responseFactory = new \Slim\Psr7\Factory\ResponseFactory();
        $response = $responseFactory->createResponse(500);
        $response->getBody()->write(json_encode([
            'message' => 'Application Error: ' . $e->getMessage(),
            'file' => basename($e->getFile()),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(500);
    }
});

$app->run();
