<?php

/**
 * Manual Authentication Fix Script
 * Directly sets environment variables and tests authentication
 * Use this if .env loading is not working
 */

echo "=== Manual Authentication Fix ===\n\n";

// Set environment variables manually based on production .env file
// These values should match your actual production setup
$envConfig = [
    'APP_ENV' => 'production',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => 'localhost',  // Change if your DB is on different host
    'DB_DATABASE' => 'haslim_bgofis',  // Your actual database name
    'DB_USERNAME' => 'haslim_bgofis',  // Your actual database username
    'DB_PASSWORD' => 'Fener1907****',  // Your actual database password
    'JWT_SECRET' => '7x9K2mN5pQ8rT3wV6yZ1aB4cD7eF0gH3jK5lM8nO2pS5uV8yX1bC4dE7fG0hJ3kL6',
    'JWT_EXPIRE' => '7200'
];

echo "Using manual environment configuration:\n";
echo "  DB_HOST: {$envConfig['DB_HOST']}\n";
echo "  DB_DATABASE: {$envConfig['DB_DATABASE']}\n";
echo "  DB_USERNAME: {$envConfig['DB_USERNAME']}\n";
echo "  DB_PASSWORD: " . (empty($envConfig['DB_PASSWORD']) ? 'EMPTY' : 'SET') . "\n";
echo "  JWT_SECRET: " . (empty($envConfig['JWT_SECRET']) ? 'NOT SET' : 'SET') . "\n\n";

// Set environment variables
foreach ($envConfig as $key => $value) {
    $_ENV[$key] = $value;
}

// Test database connection
try {
    $capsule = new \Illuminate\Database\Capsule\Manager();
    $capsule->addConnection([
        'driver' => $envConfig['DB_CONNECTION'],
        'host' => $envConfig['DB_HOST'],
        'database' => $envConfig['DB_DATABASE'],
        'username' => $envConfig['DB_USERNAME'],
        'password' => $envConfig['DB_PASSWORD'],
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
        // Get first user
        $firstUser = \Illuminate\Database\Capsule\Manager::table('users')
            ->select('id', 'email', 'name')
            ->first();
            
        echo "\nFirst user in database:\n";
        echo "  ID: {$firstUser->id}\n";
        echo "  Email: {$firstUser->email}\n";
        echo "  Name: {$firstUser->name}\n";
        
        // Test authentication
        echo "\n=== Testing Authentication ===\n";
        
        // Create a test password hash if needed
        $testPassword = 'password'; // Change this to actual password
        echo "Testing with known user and password...\n";
        
        $authService = new \App\Services\AuthService();
        
        // Try with actual user from database but need correct password
        echo "To test authentication, update this script with:\n";
        echo "  - Correct password for user: {$firstUser->email}\n";
        echo "  - Or change \$testPassword variable in this script\n\n";
        
        // Show curl command for testing
        echo "Test with curl:\n";
        echo "curl -X POST \"https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login\" \\\n";
        echo "     -H \"Content-Type: application/json\" \\\n";
        echo "     -d '{\"email\":\"{$firstUser->email}\",\"password\":\"ACTUAL_PASSWORD\"}'\n\n";
        
        // Create test token with dummy data to verify JWT is working
        try {
            $payload = [
                'iss' => 'bgaofis',
                'sub' => $firstUser->id,
                'jti' => 'test-token',
                'exp' => time() + 3600,
                'permissions' => []
            ];
            $token = \Firebase\JWT\JWT::encode($payload, $envConfig['JWT_SECRET'], 'HS256');
            echo "✓ JWT encoding working\n";
            echo "  Test token: " . substr($token, 0, 20) . "...\n";
        } catch (Exception $e) {
            echo "✗ JWT encoding failed: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "⚠ No users found in database\n";
        echo "You need to create users first\n";
        echo "Run: php database/seed.php\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "\nPossible solutions:\n";
    echo "1. Check database server is running\n";
    echo "2. Verify database credentials in \$envConfig array\n";
    echo "3. Update the configuration at the top of this script\n";
    echo "4. Check database user permissions\n";
}

echo "\n=== Database Seeding (if needed) ===\n";
echo "If no users exist, you can create an admin user:\n";

$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
echo "SQL to create admin user:\n";
echo "INSERT INTO users (email, password, name, email_verified_at, created_at, updated_at) \n";
echo "VALUES ('admin@bgaofis.com', '$adminPassword', 'Admin User', NOW(), NOW(), NOW());\n\n";

echo "=== Fix Complete ===\n";
echo "If database connection successful:\n";
echo "1. Update \$testPassword with actual user password\n";
echo "2. Test login with the curl command shown above\n";
echo "3. Verify frontend works with correct credentials\n";
