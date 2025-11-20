<?php

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
$envFile = ".env.$appEnv";
if ($appEnv && file_exists($envPath . '/' . $envFile)) {
    Dotenv::createImmutable($envPath, $envFile)->safeLoad();
}

$config = require __DIR__ . '/../config/app.php';

date_default_timezone_set($config['timezone'] ?? 'UTC');

$capsule = new Capsule();
$capsule->addConnection([
    'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
    'database' => $_ENV['DB_DATABASE'] ?? 'bgaofis',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => ''
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$app = AppFactory::create();

// Add OPTIONS handler FIRST, before routing middleware
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

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Enhanced CORS handling for all requests
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

// Disable built-in error middleware to use our custom error handling
// $app->addErrorMiddleware(
//     $config['debug'] ?? false,
//     true,
//     true
// );

// Add detailed error handling for debugging
$app->add(function($request, $handler) {
    try {
        return $handler->handle($request);
    } catch (Exception $e) {
        // Log the error
        error_log("Slim Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        
        // Return detailed error response
        $response = $handler->handle($request);
        $response->getBody()->rewind();
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

return $app;
