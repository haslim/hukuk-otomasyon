<?php

echo "=== DIRECT MENU RESTORATION (Raw MySQL) ===\n";
echo "Restoring menu data using direct MySQL connection...\n\n";

// Database configuration from .env
$dbHost = 'localhost';
$dbName = 'haslim_bgofis';
$dbUser = 'haslim_bgofis';
$dbPass = 'Fener1907****';

try {
    // Create direct MySQL connection
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "✓ Database connection established\n";
    
    // Check current menu state
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items");
    $menuCount = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_permissions");
    $permissionCount = $result->fetch_assoc()['count'];
    
    echo "Current state:\n";
    echo "- Menu items: $menuCount\n";
    echo "- Menu permissions: $permissionCount\n\n";
    
    if ($menuCount > 0) {
        echo "WARNING: Menu data already exists. Clearing existing data...\n";
        $conn->query("DELETE FROM menu_permissions");
        $conn->query("DELETE FROM menu_items");
        echo "✓ Existing menu data cleared\n";
    }
    
    // Read and execute the SQL restoration file
    $sqlFile = __DIR__ . '/arabuluculuk-menu-update.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL restoration file not found: $sqlFile");
    }
    
    echo "Executing menu restoration SQL...\n";
    
    // Read SQL file
    $sql = file_get_contents($sqlFile);
    
    // Split into individual statements and execute
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || in_array(substr($statement, 0, 2), ['--', '/*'])) {
            continue;
        }
        
        // Skip ALTER TABLE statements that might fail
        if (strpos(strtoupper($statement), 'ALTER TABLE') !== false) {
            continue;
        }
        
        if (!$conn->query($statement)) {
            echo "Warning: " . $conn->error . "\n";
        }
    }
    
    echo "✓ Menu restoration SQL executed\n\n";
    
    // Verify restoration
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_items");
    $newMenuCount = $result->fetch_assoc()['count'];
    
    $result = $conn->query("SELECT COUNT(*) as count FROM menu_permissions");
    $newPermissionCount = $result->fetch_assoc()['count'];
    
    echo "Restoration complete!\n";
    echo "- Menu items restored: $newMenuCount\n";
    echo "- Menu permissions restored: $newPermissionCount\n\n";
    
    // Show menu structure
    echo "Menu Structure:\n";
    echo "================\n";
    
    $result = $conn->query("
        SELECT 
            m1.id, m1.path, m1.label, m1.icon, m1.sort_order, m1.parent_id,
            m2.label as parent_label
        FROM menu_items m1
        LEFT JOIN menu_items m2 ON m1.parent_id = m2.id
        ORDER BY m1.sort_order
    ");
    
    while ($row = $result->fetch_assoc()) {
        $indent = $row['parent_id'] ? '  └─ ' : '';
        echo "{$indent}{$row['label']} ({$row['path']})\n";
    }
    
    $conn->close();
    
    echo "\n✓ Direct menu restoration completed successfully!\n";
    echo "The application menu navigation should now be working.\n";
    
} catch (Exception $e) {
    echo "ERROR during direct restoration: " . $e->getMessage() . "\n";
    if (isset($conn)) {
        $conn->close();
    }
    exit(1);
}