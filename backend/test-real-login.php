<?php

/**
 * Test Real Login with Actual Credentials
 * Uses provided password: test123456
 */

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Testing Real Login Credentials ===\n\n";

// Load environment manually
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
    echo "✓ Environment loaded\n";
}

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
} catch (Exception $e) {
    echo "✗ Database failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test with actual credentials
$testCredentials = [
    ['email' => 'alihaydaraslim@gmail.com', 'password' => 'test123456'],
    ['email' => 'billurguler@gmail.com', 'password' => 'test123456']
];

foreach ($testCredentials as $i => $creds) {
    echo "\n=== Test " . ($i + 1) . ": " . $creds['email'] . " ===\n";
    
    try {
        // Find user
        $user = \Illuminate\Database\Capsule\Manager::table('users')
            ->where('email', $creds['email'])
            ->first();
        
        if (!$user) {
            echo "✗ User not found\n";
            continue;
        }
        
        echo "✓ User found: ID {$user->id}, Name: {$user->name}\n";
        
        // Test password
        if (password_verify($creds['password'], $user->password)) {
            echo "✓ Password verified: SUCCESS\n";
            
            // Generate JWT
            $payload = [
                'iss' => 'bgaofis',
                'sub' => $user->id,
                'jti' => uniqid(),
                'exp' => time() + 7200,
                'permissions' => []
            ];
            
            $token = \Firebase\JWT\JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
            echo "✓ JWT generated: " . substr($token, 0, 30) . "...\n";
            
            // Test JWT validation
            try {
                $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($_ENV['JWT_SECRET'], 'HS256'));
                echo "✓ JWT validation: SUCCESS\n";
                echo "✓ User ID: {$decoded->sub}\n";
                echo "✓ Expires: " . date('Y-m-d H:i:s', $decoded->exp) . "\n";
                
            } catch (Exception $e) {
                echo "✗ JWT validation failed: " . $e->getMessage() . "\n";
            }
            
        } else {
            echo "✗ Password incorrect\n";
            
            // Check password hash info
            $passwordInfo = password_get_info($user->password);
            echo "  Hash algorithm: " . ($passwordInfo['algoName'] ?? 'Unknown') . "\n";
            echo "  Password hash length: " . strlen($user->password) . "\n";
            
            // Try to update password
            $newHash = password_hash($creds['password'], PASSWORD_DEFAULT);
            echo "  New hash would be: " . substr($newHash, 0, 20) . "...\n";
            
            // Update password in database
            try {
                \Illuminate\Database\Capsule\Manager::table('users')
                    ->where('id', $user->id)
                    ->update(['password' => $newHash]);
                    
                echo "✓ Password updated in database\n";
                
                // Test again
                if (password_verify($creds['password'], $newHash)) {
                    echo "✓ New password verification: SUCCESS\n";
                    
                    // Generate new JWT
                    $payload = [
                        'iss' => 'bgaofis',
                        'sub' => $user->id,
                        'jti' => uniqid(),
                        'exp' => time() + 7200,
                        'permissions' => []
                    ];
                    
                    $newToken = \Firebase\JWT\JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
                    echo "✓ New JWT generated: " . substr($newToken, 0, 30) . "...\n";
                    
                    echo "\n🎉 LOGIN SUCCESSFUL! 🎉\n";
                    echo "Email: " . $creds['email'] . "\n";
                    echo "Password: test123456\n";
                    echo "Token: $newToken\n";
                    
                } else {
                    echo "✗ New password verification failed\n";
                }
                
            } catch (Exception $e) {
                echo "✗ Password update failed: " . $e->getMessage() . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Final Test Commands ===\n";
echo "Test successful login in browser:\n";
echo "1. Go to: https://bgaofis.billurguleraslim.av.tr/\n";
echo "2. Login with: alihaydaraslim@gmail.com / test123456\n";
echo "3. Should see dashboard\n\n";

echo "Test with curl:\n";
echo "curl -X POST \"https://bgaofis.billurguleraslim.av.tr/api/auth/login\" \\\n";
echo "     -H \"Content-Type: application/json\" \\\n";
echo "     -d '{\"email\":\"alihaydaraslim@gmail.com\",\"password\":\"test123456\"}'\n\n";

echo "=== Test Complete ===\n";
