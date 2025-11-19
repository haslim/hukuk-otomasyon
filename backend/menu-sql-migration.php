<?php
/**
 * Direct SQL Menu Migration
 */

echo "BGAofis Menu SQL Migration\n";
echo "=============================\n\n";

try {
    // Load environment
    $basePath = __DIR__;
    require_once $basePath . '/vendor/autoload.php';

    if (file_exists($basePath . '/.env')) {
        Dotenv\Dotenv::createImmutable($basePath)->safeLoad();
    }

    // Database connection details
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $database = $_ENV['DB_DATABASE'] ?? 'bgaofis';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    echo "Database: $database\n";
    echo "Host: $host\n\n";

    // Connect with MySQLi (fallback)
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "MySQL connection established successfully!\n\n";

    // Create menu_items table
    echo "Creating menu_items table...\n";
    $menuItemsSQL = "
        CREATE TABLE IF NOT EXISTS `menu_items` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `path` VARCHAR(255) NOT NULL UNIQUE,
            `label` VARCHAR(255) NOT NULL,
            `icon` VARCHAR(255) NOT NULL,
            `sort_order` INT NOT NULL DEFAULT 0,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NULL DEFAULT NULL,
            `updated_at` TIMESTAMP NULL DEFAULT NULL,
            `deleted_at` TIMESTAMP NULL DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if ($conn->query($menuItemsSQL)) {
        echo "✓ menu_items table created successfully\n";
    } else {
        echo "✗ Error creating menu_items table: " . $conn->error . "\n";
    }

    // Create menu_permissions table
    echo "\nCreating menu_permissions table...\n";
    $menuPermissionsSQL = "
        CREATE TABLE IF NOT EXISTS `menu_permissions` (
            `id` CHAR(36) NOT NULL PRIMARY KEY,
            `role_id` CHAR(36) NOT NULL,
            `menu_item_id` CHAR(36) NOT NULL,
            `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` TIMESTAMP NULL DEFAULT NULL,
            `updated_at` TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_role_menu` (`role_id`, `menu_item_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    if ($conn->query($menuPermissionsSQL)) {
        echo "✓ menu_permissions table created successfully\n";
    } else {
        echo "✗ Error creating menu_permissions table: " . $conn->error . "\n";
    }

    echo "\nMenu tables migration completed!\n";
    $conn->close();

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
}
