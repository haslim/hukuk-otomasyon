<?php

/**
 * Production Authentication Test Script
 * Tests authentication with actual production credentials
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

echo "=== Production Authentication Test ===\n\n";

// Show current environment (without passwords)
echo "Environment Configuration:\n";
echo "  APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";
echo "  DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
echo "  DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
echo "  DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "\n";
echo "  DB_PASSWORD: " . (empty($_ENV['DB_PASSWORD']) ? 'EMPTY' : 'SET') . "\n";
echo "  JWT_SECRET: " . (empty($_ENV['JWT_SECRET']) ? 'NOT SET' : 'SET') . "\n\n";

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
    
    // Test users table
    $userCount = \Illuminate\Database\Capsule\Manager::table('users')->count();
    echo "✓ Users table accessible - Total users: $userCount\n";
    
    if ($userCount > 0) {
        // Get first user for testing
        $firstUser = \Illuminate\Database\Capsule\Manager::table('users')
            ->select('id', 'email', 'name')
            ->first();
            
        echo "\nFirst user in database:\n";
        echo "  ID: {$firstUser->id}\n";
        echo "  Email: {$firstUser->email}\n";
        echo "  Name: {$firstUser->name}\n";
        
        // Test authentication with first user
        echo "\n=== Testing Login with First User ===\n";
        echo "Email: {$firstUser->email}\n";
        echo "Password: [You need to provide the password]\n";
        
        // Test JWT configuration
        $jwtSecret = $_ENV['JWT_SECRET'] ?? null;
        if ($jwtSecret) {
            echo "✓ JWT secret configured (length: " . strlen($jwtSecret) . ")\n";
        } else {
            echo "✗ JWT secret not configured\n";
        }
        
        echo "\nTo test login, run:\n";
        echo "curl -X POST \"https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login\" \\\n";
        echo "     -H \"Content-Type: application/json\" \\\n";
        echo "     -d '{\"email\":\"{$firstUser->email}\",\"password\":\"YOUR_PASSWORD\"}'\n";
        
    } else {
        echo "⚠ No users found in database\n";
        echo "You need to run database migrations/seeds to create users\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPossible solutions:\n";
    echo "1. Check database server is running\n";
    echo "2. Verify database credentials in .env file\n";
    echo "3. Check database user permissions\n";
    echo "4. Verify database name is correct\n";
}

echo "\n=== Test Complete ===\n";
