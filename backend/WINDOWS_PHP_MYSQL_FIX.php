<?php
/**
 * Windows PHP MySQL Extension Fix
 * This script helps diagnose and fix PDO MySQL extension issues on Windows
 */

echo "Windows PHP MySQL Extension Fix\n";
echo "==============================\n\n";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";
echo "PHP OS: " . PHP_OS . "\n\n";

// Check if PDO MySQL extension is loaded
echo "Extension Status:\n";
echo "PDO: " . (extension_loaded('pdo') ? "✅ LOADED" : "❌ NOT LOADED") . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ LOADED" : "❌ NOT LOADED") . "\n";
echo "MySQL Native Driver: " . (extension_loaded('mysqlnd') ? "✅ LOADED" : "❌ NOT LOADED") . "\n\n";

// Show PHP configuration file location
echo "PHP Configuration:\n";
echo "php.ini location: " . php_ini_loaded_file() . "\n";
echo "Scanned ini files: " . php_ini_scanned_files() . "\n\n";

// Show available PDO drivers
if (extension_loaded('pdo')) {
    echo "Available PDO Drivers:\n";
    $drivers = PDO::getAvailableDrivers();
    if (empty($drivers)) {
        echo "❌ No PDO drivers available\n";
    } else {
        foreach ($drivers as $driver) {
            echo "✅ " . $driver . "\n";
        }
    }
} else {
    echo "❌ PDO extension not loaded\n";
}

echo "\n";

// Show extension directory
echo "Extension Directory:\n";
echo "extension_dir: " . ini_get('extension_dir') . "\n\n";

// Check if pdo_mysql.dll exists
$extensionDir = ini_get('extension_dir');
if ($extensionDir && file_exists($extensionDir . '/pdo_mysql.dll')) {
    echo "✅ pdo_mysql.dll found in extension directory\n";
} else {
    echo "❌ pdo_mysql.dll NOT found in extension directory\n";
    
    // Try to find it in common locations
    $commonPaths = [
        dirname(PHP_BINARY) . '/ext',
        'C:/php/ext',
        'C:/xampp/php/ext',
        'C:/wamp64/bin/php/php' . PHP_VERSION . '/ext'
    ];
    
    echo "\nChecking common extension paths:\n";
    foreach ($commonPaths as $path) {
        if (file_exists($path . '/pdo_mysql.dll')) {
            echo "✅ Found at: " . $path . "/pdo_mysql.dll\n";
        } else {
            echo "❌ Not found at: " . $path . "/pdo_mysql.dll\n";
        }
    }
}

echo "\n";

// Provide solutions
echo "SOLUTIONS:\n";
echo "==========\n\n";

echo "Option 1: Enable extension in php.ini\n";
echo "--------------------------------------\n";
echo "1. Find your php.ini file: " . php_ini_loaded_file() . "\n";
echo "2. Uncomment or add this line:\n";
echo "   extension=pdo_mysql\n";
echo "3. Restart your web server (Apache/Nginx/IIS)\n\n";

echo "Option 2: Install XAMPP/WAMP (Recommended for Windows)\n";
echo "-----------------------------------------------------\n";
echo "1. Download XAMPP from https://www.apachefriends.org/\n";
echo "2. Install XAMPP with Apache + MySQL + PHP\n";
echo "3. Use the PHP that comes with XAMPP\n";
echo "4. Update your project's PHP path to use XAMPP's PHP\n\n";

echo "Option 3: Manual installation\n";
echo "-----------------------------\n";
echo "1. Download PHP for Windows from https://windows.php.net/download/\n";
echo "2. Extract to C:/php/\n";
echo "3. Copy php.ini-development to php.ini\n";
echo "4. Uncomment extension=pdo_mysql in php.ini\n";
echo "5. Add C:/php to your PATH environment variable\n\n";

echo "Option 4: Use Docker (Alternative)\n";
echo "---------------------------------\n";
echo "1. Install Docker Desktop\n";
echo "2. Use a PHP Docker container with MySQL extensions\n";
echo "3. Mount your project directory\n\n";

// Test database connection if extension is available
if (extension_loaded('pdo_mysql')) {
    echo "Testing Database Connection:\n";
    echo "===========================\n";
    
    // Load environment if .env exists
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }
    
    if (isset($_ENV['DB_HOST'], $_ENV['DB_DATABASE'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'])) {
        try {
            $pdo = new PDO(
                "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
                $_ENV["DB_USERNAME"],
                $_ENV["DB_PASSWORD"]
            );
            echo "✅ Database connection: SUCCESS\n";
            
            // Check if users table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() > 0) {
                echo "✅ Users table: EXISTS\n";
                
                // Check for admin user
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
                    
                    // Update password to ensure it's test123456
                    $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE email = ?");
                    $hashedPassword = password_hash("test123456", PASSWORD_DEFAULT);
                    $stmt->execute([$hashedPassword, "alihaydaraslim@gmail.com"]);
                    echo "✅ Updated admin password to: test123456\n";
                }
            } else {
                echo "❌ Users table not found. Please run migrations first.\n";
            }
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "❌ Database credentials not found in .env file\n";
    }
}

echo "\n";