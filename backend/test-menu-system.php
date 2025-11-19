<?php
/**
 * Test Menu System
 */

echo "Testing Menu System Implementation\n";
echo "================================\n\n";

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

    // Connect with MySQLi
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    echo "✓ Database connected successfully\n";

    // Test 1: Check if tables exist
    echo "\n--- Table Existence Check ---\n";
    $result = $conn->query("SHOW TABLES LIKE 'menu_items'");
    $menuItemsExists = $result->num_rows > 0;
    echo "menu_items table: " . ($menuItemsExists ? "EXISTS" : "MISSING") . "\n";

    $result = $conn->query("SHOW TABLES LIKE 'menu_permissions'");
    $menuPermsExists = $result->num_rows > 0;
    echo "menu_permissions table: " . ($menuPermsExists ? "EXISTS" : "MISSING") . "\n";

    // Test 2: Check data
    if ($menuItemsExists && $menuPermsExists) {
        echo "\n--- Data Check ---\n";
        $result = $conn->query("SELECT COUNT(*) as count FROM menu_items");
        $row = $result->fetch_assoc();
        echo "Menu items count: " . $row['count'] . "\n";

        $result = $conn->query("SELECT COUNT(*) as count FROM menu_permissions");
        $row = $result->fetch_assoc();
        echo "Menu permissions count: " . $row['count'] . "\n";

        // Test 3: Check role assignments
        echo "\n--- Role Menu Assignment ---\n";
        $query = "
            SELECT 
                r.name as role_name,
                COUNT(mp.id) as visible_menus
            FROM roles r
            LEFT JOIN menu_permissions mp ON r.id = mp.role_id AND mp.is_visible = 1
            GROUP BY r.id, r.name
            ORDER BY r.name
        ";
        $result = $conn->query($query);
        while ($row = $result->fetch_assoc()) {
            echo "Role: {$row['role_name']} - Visible menus: {$row['visible_menus']}\n";
        }

        // Test 4: Check specific menu items for lawyer role
        echo "\n--- Lawyer Role Menu Check ---\n";
        $query = "
            SELECT 
                mi.path,
                mi.label,
                mp.is_visible
            FROM menu_permissions mp
            JOIN menu_items mi ON mp.menu_item_id = mi.id
            JOIN roles r ON mp.role_id = r.id
            WHERE r.key = 'lawyer'
            ORDER BY mi.sort_order
        ";
        $result = $conn->query($query);
        $restrictedCount = 0;
        while ($row = $result->fetch_assoc()) {
            $status = $row['is_visible'] ? 'VISIBLE' : 'HIDDEN';
            echo "  {$row['path']} - {$row['label']} - $status\n";
            if (!$row['is_visible']) $restrictedCount++;
        }
        echo "Restricted menus for lawyer: $restrictedCount\n";

        // Test 5: API endpoint test (simulation)
        echo "\n--- API Simulation ---\n";
        $query = "
            SELECT 
                mi.path,
                mi.label,
                mi.icon,
                mi.sort_order
            FROM menu_permissions mp
            JOIN menu_items mi ON mp.menu_item_id = mi.id
            JOIN user_roles ur ON mp.role_id = ur.role_id
            WHERE ur.user_id = (SELECT id FROM users LIMIT 1) 
            AND mp.is_visible = 1
            ORDER BY mi.sort_order
        ";
        $result = $conn->query($query);
        echo "Simulated user menu items:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['label']} ({$row['path']})\n";
        }
    }

    echo "\n✓ Menu system test completed successfully!\n";
    $conn->close();

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
}
