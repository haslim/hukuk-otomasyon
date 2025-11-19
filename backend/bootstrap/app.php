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
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Allow CORS preflight requests (OPTIONS) for all routes
$app->options('/{routes:.+}', function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    return $response->withStatus(200);
});

// Add comprehensive CORS middleware for all requests
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    
    // Get allowed origin from environment or allow all for development
    $allowedOrigin = $_ENV['CORS_ORIGIN'] ?? '*';
    
    return $response
        ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');
});

$app->addErrorMiddleware(
    $config['debug'] ?? false,
    true,
    true
);

return $app;
