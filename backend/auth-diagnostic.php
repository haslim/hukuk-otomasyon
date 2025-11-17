<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load environment
$envPath = __DIR__;
if (file_exists($envPath . '/.env')) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

// Test JWT token from the error logs
$testToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJiZ2FvZmlzIiwic3ViIjoyMiwianRpIjoiYWVhMjdlZTMtMDVlZi00NTZhLWE4ZmItZjg1OTM5MDhlZDdiIiwiZXhwIjoxNzYzMjI4ODg3LCJwZXJtaXNzaW9ucyI6W119.7P5xSWx3RrrAksAiphcxFQJuA5RGI981ui8fFIuUph0';

echo "=== JWT Authentication Diagnostic ===\n\n";

// Check current JWT secret
$currentSecret = $_ENV['JWT_SECRET'] ?? 'NOT_SET';
echo "Current JWT Secret: " . substr($currentSecret, 0, 20) . "...\n\n";

// Test with different secrets
$secrets = [
    'current_env' => $currentSecret,
    'production_default' => 'your-super-secret-jwt-key-change-this-in-production',
    'env_file_secret' => '7x9K2mN5pQ8rT3wV6yZ1aB4cD7eF0gH3jK5lM8nO2pS5uV8yX1bC4dE7fG0hJ3kL6'
];

foreach ($secrets as $name => $secret) {
    echo "Testing with secret: $name\n";
    try {
        $decoded = JWT::decode($testToken, new Key($secret, 'HS256'));
        echo "✅ SUCCESS: Token decoded successfully\n";
        echo "User ID: " . $decoded->sub . "\n";
        echo "Expires: " . date('Y-m-d H:i:s', $decoded->exp) . "\n";
        echo "Current time: " . date('Y-m-d H:i:s') . "\n";
        echo "Token expired: " . ($decoded->exp < time() ? 'YES' : 'NO') . "\n\n";
    } catch (Exception $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n\n";
    }
}

// Check database connection
echo "=== Database Connection Test ===\n";
try {
    $capsule = new Illuminate\Database\Capsule\Manager();
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
    
    // Test user query
    $user = $capsule->table('users')->where('id', 22)->first();
    if ($user) {
        echo "✅ Database connection successful\n";
        echo "✅ User ID 22 found: " . $user->email . "\n";
    } else {
        echo "❌ User ID 22 not found in database\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Ensure JWT_SECRET is consistent across all environment files\n";
echo "2. Check if token has expired\n";
echo "3. Verify user exists in database\n";
echo "4. Update .env.production with the correct JWT secret\n";