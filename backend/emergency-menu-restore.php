<?php

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;

echo "=== EMERGENCY MENU RESTORATION ===\n";
echo "Restoring menu data immediately...\n\n";

try {
    // Check if menu tables exist
    $tables = DB::select("SHOW TABLES LIKE 'menu_%'");
    if (empty($tables)) {
        echo "ERROR: Menu tables not found. Please run migrations first.\n";
        exit(1);
    }
    
    // Check current menu state
    $menuCount = DB::table('menu_items')->count();
    $permissionCount = DB::table('menu_permissions')->count();
    
    echo "Current state:\n";
    echo "- Menu items: $menuCount\n";
    echo "- Menu permissions: $permissionCount\n\n";
    
    if ($menuCount > 0) {
        echo "WARNING: Menu data already exists. Clearing existing data...\n";
        DB::table('menu_permissions')->delete();
        DB::table('menu_items')->delete();
        echo "✓ Existing menu data cleared\n";
    }
    
    // Read and execute the SQL restoration file
    $sqlFile = __DIR__ . '/arabuluculuk-menu-update.sql';
    if (!file_exists($sqlFile)) {
        echo "ERROR: SQL restoration file not found: $sqlFile\n";
        exit(1);
    }
    
    echo "Executing menu restoration SQL...\n";
    
    // Read SQL file
    $sql = file_get_contents($sqlFile);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (empty($statement) || in_array(substr($statement, 0, 2), ['--', '/*'])) {
            continue;
        }
        
        try {
            DB::statement($statement);
        } catch (Exception $e) {
            // Ignore errors for statements that might already exist (like ALTER TABLE)
            if (strpos($e->getMessage(), 'already exists') === false && 
                strpos($e->getMessage(), 'Duplicate column') === false) {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "✓ Menu restoration SQL executed\n\n";
    
    // Verify restoration
    $newMenuCount = DB::table('menu_items')->count();
    $newPermissionCount = DB::table('menu_permissions')->count();
    
    echo "Restoration complete!\n";
    echo "- Menu items restored: $newMenuCount\n";
    echo "- Menu permissions restored: $newPermissionCount\n\n";
    
    // Show menu structure
    echo "Menu Structure:\n";
    echo "================\n";
    
    $menus = DB::table('menu_items')
        ->leftJoin('menu_items as parent', 'menu_items.parent_id', '=', 'parent.id')
        ->select('menu_items.*', 'parent.label as parent_label')
        ->orderBy('menu_items.sort_order')
        ->get();
    
    foreach ($menus as $menu) {
        $indent = $menu->parent_id ? '  └─ ' : '';
        echo "{$indent}{$menu->label} ({$menu->path})\n";
    }
    
    echo "\n✓ Emergency menu restoration completed successfully!\n";
    echo "The application menu navigation should now be working.\n";
    
} catch (Exception $e) {
    echo "ERROR during emergency restoration: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}