<?php

/**
 * Final Authentication Test with Real User
 * Tests login with actual user from database
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment
$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

echo "=== Final Authentication Test ===\n\n";

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
    
    echo "✓ Database connected\n";
    
    // Get actual users
    $users = \Illuminate\Database\Capsule\Manager::table('users')
        ->select('id', 'email', 'name', 'created_at')
        ->orderBy('id')
        ->get();
    
    echo "✓ Found " . count($users) . " users in database\n\n";
    
    foreach ($users as $user) {
        echo "User ID: {$user->id}\n";
        echo "Email: {$user->email}\n";
        echo "Name: {$user->name}\n";
        echo "Created: {$user->created_at}\n";
        echo "---\n";
    }
    
    echo "\n=== Login Test Commands ===\n";
    echo "Test these users with curl:\n\n";
    
    foreach ($users as $user) {
        echo "Test User {$user->id} ({$user->name}):\n";
        echo "curl -X POST \"https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login\" \\\n";
        echo "     -H \"Content-Type: application/json\" \\\n";
        echo "     -d '{\"email\":\"{$user->email}\",\"password\":\"PASSWORD_HERE\"}'\n\n";
    }
    
    echo "Replace PASSWORD_HERE with actual password for each user\n\n";
    
    // Test JWT with actual user data
    if (count($users) > 0) {
        $firstUser = $users[0];
        
        echo "=== JWT Token Test ===\n";
        try {
            $payload = [
                'iss' => 'bgaofis',
                'sub' => $firstUser->id,
                'jti' => 'test-token-' . time(),
                'exp' => time() + 7200,
                'permissions' => []
            ];
            
            $token = \Firebase\JWT\JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
            echo "✓ JWT Token generated successfully\n";
            echo "  User ID: {$firstUser->id}\n";
            echo "  Token: " . substr($token, 0, 30) . "...\n";
            echo "  Expires: " . date('Y-m-d H:i:s', $payload['exp']) . "\n";
            
            // Test token validation
            try {
                $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($_ENV['JWT_SECRET'], 'HS256'));
                echo "✓ JWT Token validation successful\n";
                echo "  Decoded User ID: " . $decoded->sub . "\n";
            } catch (Exception $e) {
                echo "✗ JWT Token validation failed: " . $e->getMessage() . "\n";
            }
            
        } catch (Exception $e) {
            echo "✗ JWT Token generation failed: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Frontend Test Instructions ===\n";
echo "1. Use one of the emails above with correct password\n";
echo "2. Test in frontend login form\n";
echo "3. Check browser network tab for successful login\n";
echo "4. Verify dashboard loads after login\n\n";

echo "=== Expected Results ===\n";
echo "✅ 405 Method Not Allowed: Should be resolved\n";
echo "✅ 401 Unauthorized: Should work with valid credentials\n";
echo "✅ Dashboard: Should load with authenticated user\n";
echo "✅ API Endpoints: Should return data with JWT token\n\n";

echo "=== Test Complete ===\n";
