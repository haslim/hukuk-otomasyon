<?php
echo "Testing Database Connection\n";
echo "=========================\n\n";

try {
    // Load environment
    $basePath = __DIR__;
    require_once $basePath . '/vendor/autoload.php';

    if (file_exists($basePath . '/.env')) {
        Dotenv\Dotenv::createImmutable($basePath)->safeLoad();
    }

    echo "DB_CONNECTION: " . ($_ENV['DB_CONNECTION'] ?? 'not set') . "\n";
    echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
    echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
    echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "\n\n";

    // Test PDO MySQL extension
    if (extension_loaded('pdo_mysql')) {
        echo "PDO MySQL extension: LOADED\n";
    } else {
        echo "PDO MySQL extension: NOT LOADED\n";
    }

    // Test direct PDO connection
    $dsn = "mysql:host=" . ($_ENV['DB_HOST'] ?? 'localhost') . ";dbname=" . ($_ENV['DB_DATABASE'] ?? 'test') . ";charset=utf8mb4";
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    try {
        $pdo = new PDO($dsn, $username, $password);
        echo "PDO Connection: SUCCESS\n\n";

        // Test if menu_items table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'menu_items'");
        $exists = $stmt->rowCount() > 0;
        echo "menu_items table exists: " . ($exists ? 'YES' : 'NO') . "\n";

    } catch (PDOException $e) {
        echo "PDO Connection: FAILED - " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
