<?php
require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load environment
$envPath = __DIR__;
if (file_exists($envPath . "/.env")) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

// Test database connection without Eloquent
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"]
    );
    echo "✅ Database connection successful\n";
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE id = ?");
    $stmt->execute([22]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User ID 22 found: " . $user["email"] . "\n";
    } else {
        echo "❌ User ID 22 not found\n";
        
        // Create test user if not exists
        $stmt = $pdo->prepare("INSERT INTO users (id, email, password, name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $userId = "test-user-" . uniqid();
        $stmt->execute([$userId, "test@bgaofis.com", password_hash("test123", PASSWORD_DEFAULT), "Test User"]);
        echo "✅ Created test user: test@bgaofis.com / test123\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test JWT generation and validation
try {
    $jwtSecret = $_ENV["JWT_SECRET"];
    $payload = [
        "iss" => "bgaofis",
        "sub" => "22",
        "jti" => uniqid(),
        "exp" => time() + 3600, // 1 hour
        "permissions" => ["CASE_VIEW_ALL", "CLIENT_MANAGE"]
    ];
    
    $token = JWT::encode($payload, $jwtSecret, "HS256");
    echo "✅ JWT Token generated: " . substr($token, 0, 50) . "...\n";
    
    // Test validation
    $decoded = JWT::decode($token, new Key($jwtSecret, "HS256"));
    echo "✅ JWT Token validation successful\n";
    echo "   User ID: " . $decoded->sub . "\n";
    echo "   Expires: " . date("Y-m-d H:i:s", $decoded->exp) . "\n";
    
} catch (Exception $e) {
    echo "❌ JWT test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Login ===\n";
echo "You can test login with:\n";
echo "Email: test@bgaofis.com\n";
echo "Password: test123\n";
echo "Or use existing user credentials\n";
