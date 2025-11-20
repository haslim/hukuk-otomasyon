<?php

/**
 * Simple Login Test - Bypasses Slim to test authentication directly
 * This helps isolate if the issue is with Slim or authentication
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Simple Login Test (Slim Bypass) ===\n\n";

// Load environment
$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    $lines = file($envPath . '/.env');
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
    echo "✓ Environment loaded manually\n";
}

// Test database
try {
    $capsule = new \Illuminate\Database\Capsule\Manager();
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
    echo "✓ Database connected\n";
} catch (Exception $e) {
    echo "✗ Database failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Get input
$input = json_decode(file_get_contents('php://input'), true);
echo "Login request received:\n";
echo "  Email: " . ($input['email'] ?? 'not provided') . "\n";
echo "  Password: " . (empty($input['password']) ? 'empty' : 'provided') . "\n\n";

try {
    // Find user
    $user = \Illuminate\Database\Capsule\Manager::table('users')
        ->where('email', $input['email'] ?? '')
        ->first();
    
    if (!$user) {
        echo "✗ User not found\n";
        json_response(['message' => 'Invalid credentials'], 401);
        exit;
    }
    
    echo "✓ User found: ID {$user->id}, Name: {$user->name}\n";
    
    // Test password
    if (!password_verify($input['password'] ?? '', $user->password)) {
        echo "✗ Password incorrect\n";
        json_response(['message' => 'Invalid credentials'], 401);
        exit;
    }
    
    echo "✓ Password verified\n";
    
    // Generate JWT
    $payload = [
        'iss' => 'bgaofis',
        'sub' => $user->id,
        'jti' => uniqid(),
        'exp' => time() + 7200,
        'permissions' => []
    ];
    
    $token = \Firebase\JWT\JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    echo "✓ JWT generated\n";
    
    // Return success
    json_response([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ]
    ], 200);
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    json_response(['message' => 'Login failed: ' . $e->getMessage()], 500);
}

function json_response($data, $status = 200) {
    header_remove();
    header("Content-Type: application/json");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    
    http_response_code($status);
    echo json_encode($data);
    exit;
}

echo "\n=== Test Complete ===\n";
