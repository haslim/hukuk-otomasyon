<?php
/**
 * Quick Authentication Fix
 * Run this script after installing pdo_mysql extension
 */

echo "Quick Authentication Fix\n";
echo "======================\n";

require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;

// Load environment
$envPath = __DIR__;
if (file_exists($envPath . "/.env")) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

// Test database connection
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"]
    );
    echo "✅ Database connection: SUCCESS\n";
    
    // Check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() === 0) {
        echo "❌ Users table not found. Please run migrations first.\n";
    } else {
        echo "✅ Users table: EXISTS\n";
        
        // Check for admin user
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email LIKE '%admin%'");
        $stmt->execute();
        $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)["count"];
        
        if ($adminCount === 0) {
            echo "⚠️  No admin user found. Creating one...\n";
            
            $userId = "admin-" . uniqid();
            $hashedPassword = password_hash("admin123", PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (id, email, password, name, created_at, updated_at) 
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$userId, "admin@bgaofis.com", $hashedPassword, "Admin User"]);
            
            echo "✅ Created admin user: admin@bgaofis.com / admin123\n";
        } else {
            echo "✅ Admin user: EXISTS\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please install pdo_mysql extension and check database credentials.\n";
}

// Test JWT
try {
    $token = Firebase\JWT\JWT::encode([
        "test" => "value",
        "exp" => time() + 3600
    ], $_ENV["JWT_SECRET"], "HS256");
    
    Firebase\JWT\JWT::decode($token, new Firebase\JWT\Key($_ENV["JWT_SECRET"], "HS256"));
    
    echo "✅ JWT functionality: WORKING\n";
} catch (Exception $e) {
    echo "❌ JWT functionality: FAILED - " . $e->getMessage() . "\n";
}

echo "\nNext steps:\n";
echo "1. Install pdo_mysql extension if not done\n";
echo "2. Run database migrations: php migrate.php\n";
echo "3. Test login: admin@bgaofis.com / admin123\n";
echo "4. Access frontend and test authentication\n";
