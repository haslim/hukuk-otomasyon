<?php

/**
 * Debug 500 Internal Server Error
 * Diagnoses and fixes backend application errors
 */

echo "=== Debugging 500 Internal Server Error ===\n\n";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment
$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
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
    echo "✓ Database connection established\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test User model
try {
    $user = \App\Models\User::first();
    if ($user) {
        echo "✓ User model working\n";
        echo "  First user ID: {$user->id}\n";
        echo "  First user email: {$user->email}\n";
    } else {
        echo "⚠ No users found in database\n";
    }
} catch (Exception $e) {
    echo "✗ User model failed: " . $e->getMessage() . "\n";
    echo "  Check: User model exists, Eloquent is working\n";
}

// Test AuthService
try {
    $authService = new \App\Services\AuthService();
    echo "✓ AuthService instantiated\n";
} catch (Exception $e) {
    echo "✗ AuthService failed: " . $e->getMessage() . "\n";
    echo "  Check: AuthService class exists, dependencies loaded\n";
}

// Test JWT functionality
try {
    $payload = [
        'iss' => 'bgaofis',
        'sub' => 1,
        'jti' => 'test',
        'exp' => time() + 3600,
        'permissions' => []
    ];
    $token = \Firebase\JWT\JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
    echo "✓ JWT encoding working\n";
    echo "  Token length: " . strlen($token) . "\n";
} catch (Exception $e) {
    echo "✗ JWT encoding failed: " . $e->getMessage() . "\n";
    echo "  Check: JWT secret is correct, Firebase\JWT library loaded\n";
}

// Test manual authentication attempt
echo "\n=== Manual Authentication Test ===\n";
try {
    // Get a real user
    $testUser = \Illuminate\Database\Capsule\Manager::table('users')->first();
    if ($testUser) {
        echo "Testing with user: {$testUser->email} (ID: {$testUser->id})\n";
        
        // Test AuthService attempt
        $authService = new \App\Services\AuthService();
        
        // Test with wrong password first
        $result1 = $authService->attempt($testUser->email, 'wrong_password');
        if ($result1 === null) {
            echo "✓ Wrong password correctly rejected\n";
        } else {
            echo "✗ Wrong password was accepted (security issue!)\n";
        }
        
        // Check password hash format
        if (password_verify('wrong_password', $testUser->password)) {
            echo "✗ Password hash verification issue detected\n";
        } else {
            echo "✓ Password hash verification working correctly\n";
        }
        
        echo "  Password hash length: " . strlen($testUser->password) . "\n";
        echo "  Password hash starts with: " . substr($testUser->password, 0, 10) . "...\n";
        
        // Test if password is properly hashed
        $passwordInfo = password_get_info($testUser->password);
        if ($passwordInfo['algo'] === 0) {
            echo "✗ Password is NOT properly hashed (using plain text or old hash)\n";
            echo "  Need to update user passwords with proper hash\n";
        } else {
            echo "✓ Password is properly hashed with algorithm: " . $passwordInfo['algoName'] . "\n";
        }
        
    } else {
        echo "✗ No users found for testing\n";
    }
} catch (Exception $e) {
    echo "✗ Manual authentication test failed: " . $e->getMessage() . "\n";
    echo "  Stack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

// Check common 500 error causes
echo "\n=== Common 500 Error Checks ===\n";

// Check required files
$requiredFiles = [
    'vendor/autoload.php',
    'app/Services/AuthService.php',
    'app/Models/User.php',
    'bootstrap/app.php',
    'routes/api.php'
];

foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "✓ $file exists\n";
    } else {
        echo "✗ $file MISSING - This will cause 500 errors\n";
    }
}

// Check PHP extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'openssl', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✓ Extension $ext loaded\n";
    } else {
        echo "✗ Extension $ext NOT loaded - Install required\n";
    }
}

// Test a simple API call simulation
echo "\n=== API Call Simulation ===\n";
try {
    // Simulate POST data
    $_POST['email'] = 'alihaydaraslim@gmail.com';
    $_POST['password'] = 'test_password';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    // Create a PSR-7 request simulation
    $factory = new \Slim\Psr7\Factory\ServerRequestFactory();
    $request = $factory->createServerRequest(
        $_SERVER,
        $_FILES,
        'https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login',
        'POST',
        'php://input',
        [],
        [],
        json_decode('{"email":"alihaydaraslim@gmail.com","password":"test_password"}', true)
    );
    
    echo "✓ PSR-7 request simulation working\n";
    
    // Load the app
    $app = require __DIR__ . '/bootstrap/app.php';
    echo "✓ App loaded successfully\n";
    
} catch (Exception $e) {
    echo "✗ API simulation failed: " . $e->getMessage() . "\n";
    echo "  This is likely causing the 500 error\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Check error logs: /var/log/apache2/error.log or similar\n";
echo "2. Verify all required files exist and are readable\n";
echo "3. Ensure PHP extensions are installed\n";
echo "4. Check database user permissions\n";
echo "5. Verify user passwords are properly hashed\n";
echo "6. Test with proper login credentials\n\n";

echo "=== Quick Fix Commands ===\n";
echo "If passwords are not properly hashed:\n";
echo "1. Update user password: UPDATE users SET password = '"
    . password_hash('new_password', PASSWORD_DEFAULT) 
    . "' WHERE email = 'alihaydaraslim@gmail.com';\n";
echo "2. Try login with: alihaydaraslim@gmail.com / new_password\n\n";

echo "=== Debug Complete ===\n";
