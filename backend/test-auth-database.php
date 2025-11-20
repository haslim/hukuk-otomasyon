<?php

/**
 * Test script to check database connection and user authentication
 * Run this to diagnose 401 Unauthorized issues
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

$appEnv = $_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? getenv('APP_ENV') ?? 'production';
$envFile = ".env.$appEnv";
if ($appEnv && file_exists($envPath . '/' . $envFile)) {
    Dotenv::createImmutable($envPath, $envFile)->safeLoad();
}

echo "=== Authentication Database Test ===\n\n";

// Test database connection
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
    
    echo "✓ Database connection successful\n";
    echo "  Host: " . ($_ENV['DB_HOST'] ?? '127.0.0.1') . "\n";
    echo "  Database: " . ($_ENV['DB_DATABASE'] ?? 'bgaofis') . "\n";
    echo "  Username: " . ($_ENV['DB_USERNAME'] ?? 'root') . "\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test if users table exists and has records
try {
    $userCount = \Illuminate\Database\Capsule\Manager::table('users')->count();
    echo "✓ Users table accessible\n";
    echo "  Total users: $userCount\n";
    
    if ($userCount === 0) {
        echo "⚠ No users found in database - this may cause authentication issues\n";
    }
    
    // Show first few users (without passwords)
    $users = \Illuminate\Database\Capsule\Manager::table('users')
        ->select('id', 'email', 'name', 'email_verified_at', 'created_at')
        ->limit(5)
        ->get();
    
    echo "\nSample users:\n";
    foreach ($users as $user) {
        echo "  ID: {$user->id}, Email: {$user->email}, Name: {$user->name}\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error accessing users table: " . $e->getMessage() . "\n";
}

// Test User model
try {
    $user = \App\Models\User::first();
    if ($user) {
        echo "✓ User model working\n";
        echo "  First user ID: {$user->id}\n";
        echo "  First user email: {$user->email}\n";
    } else {
        echo "⚠ No users found via User model\n";
    }
} catch (Exception $e) {
    echo "✗ Error with User model: " . $e->getMessage() . "\n";
}

// Test JWT configuration
echo "\n=== JWT Configuration ===\n";
$jwtSecret = $_ENV['JWT_SECRET'] ?? null;
$jwtExpire = $_ENV['JWT_EXPIRE'] ?? 7200;

if ($jwtSecret) {
    echo "✓ JWT secret configured\n";
    echo "  Secret length: " . strlen($jwtSecret) . " characters\n";
} else {
    echo "✗ JWT secret not configured\n";
}

echo "✓ JWT expiration: $jwtExpire seconds\n";

// Test authentication with sample credentials
echo "\n=== Authentication Test ===\n";
$testEmail = 'admin@example.com'; // Change this to a real email in your database
$testPassword = 'password'; // Change this to the real password

echo "Testing with email: $testEmail\n";

try {
    $authService = new \App\Services\AuthService();
    $result = $authService->attempt($testEmail, $testPassword);
    
    if ($result) {
        echo "✓ Authentication successful\n";
        echo "  Token generated: " . substr($result['token'], 0, 20) . "...\n";
        echo "  User ID: " . $result['user']->id . "\n";
        echo "  User email: " . $result['user']->email . "\n";
    } else {
        echo "✗ Authentication failed\n";
        echo "  Possible causes:\n";
        echo "  - Email not found in database\n";
        echo "  - Password incorrect\n";
        echo "  - User password not properly hashed\n";
        
        // Check if user exists
        $user = \App\Models\User::where('email', $testEmail)->first();
        if ($user) {
            echo "  - User found in database (ID: {$user->id})\n";
            echo "  - Password hash length: " . strlen($user->password) . "\n";
        } else {
            echo "  - User NOT found in database\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Authentication test error: " . $e->getMessage() . "\n";
}

echo "\n=== Recommendations ===\n";
echo "1. If no users exist, run database migrations/seeds\n";
echo "2. Check environment variables in .env file\n";
echo "3. Verify database credentials and permissions\n";
echo "4. Test with actual user credentials from your database\n";
echo "5. Check if passwords are properly hashed (password_hash())\n";

echo "\n=== Test Complete ===\n";
