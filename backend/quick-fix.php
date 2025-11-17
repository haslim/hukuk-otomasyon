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
        
        // Check for specific admin user
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
        $stmt->execute(["alihaydaraslim@gmail.com"]);
        $adminCount = $stmt->fetch(PDO::FETCH_ASSOC)["count"];
        
        if ($adminCount === 0) {
            echo "⚠️  No admin user found. Creating one...\n";
            
            $userId = "admin-" . uniqid();
            $hashedPassword = password_hash("test123456", PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (id, email, password, name, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$userId, "alihaydaraslim@gmail.com", $hashedPassword, "Ali Haydar Aslim"]);
            
            echo "✅ Created admin user: alihaydaraslim@gmail.com / test123456\n";
        } else {
            echo "✅ Admin user already exists: alihaydaraslim@gmail.com\n";
            
            // Optional: Update the password to ensure it's test123456
            $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
            $hashedPassword = password_hash("test123456", PASSWORD_DEFAULT);
            $stmt->execute([$hashedPassword, "alihaydaraslim@gmail.com"]);
            echo "✅ Updated admin password to: test123456\n";
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
echo "3. Test login: alihaydaraslim@gmail.com / test123456\n";
echo "4. Access frontend and test authentication\n";
