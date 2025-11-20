<?php

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;
use Database\Seeders\MenuItemSeeder;

echo "=== FIXED MENU STRUCTURE UPDATE ===\n";
echo "Updating menu structure for mediation grouping with error handling...\n\n";

// Track operations for rollback
$operations = [];
$rollbackData = [];

try {
    // Step 1: Verify database connection
    echo "Step 1: Verifying database connection...\n";
    if (!DB::connection()->getPdo()) {
        throw new Exception("Database connection failed");
    }
    echo "✓ Database connection verified\n";
    $operations[] = 'connection_verified';

    // Step 2: Backup current menu data before making changes
    echo "\nStep 2: Backing up current menu data...\n";
    $currentMenuItems = DB::table('menu_items')->get()->toArray();
    $currentPermissions = DB::table('menu_permissions')->get()->toArray();
    
    $rollbackData = [
        'menu_items' => $currentMenuItems,
        'menu_permissions' => $currentPermissions,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Save backup to file
    $backupFile = __DIR__ . '/menu_backup_' . date('Y-m-d_H-i-s') . '.json';
    file_put_contents($backupFile, json_encode($rollbackData, JSON_PRETTY_PRINT));
    echo "✓ Backup saved to: $backupFile\n";
    $operations[] = 'backup_created';

    // Step 3: Check and run menu hierarchy migration
    echo "\nStep 3: Checking menu hierarchy structure...\n";
    $hasParentColumn = DB::schema()->hasColumn('menu_items', 'parent_id');
    
    if (!$hasParentColumn) {
        echo "Running menu hierarchy migration...\n";
        DB::schema()->table('menu_items', function ($table) {
            $table->uuid('parent_id')->nullable()->after('id');
            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
            $table->index('parent_id');
        });
        echo "✓ Menu hierarchy migration completed\n";
        $operations[] = 'migration_applied';
    } else {
        echo "✓ Parent column already exists\n";
    }

    // Step 4: Clear existing menu data with confirmation
    echo "\nStep 4: Clearing existing menu data...\n";
    $menuCount = DB::table('menu_items')->count();
    $permissionCount = DB::table('menu_permissions')->count();
    
    echo "Current data: $menuCount menu items, $permissionCount permissions\n";
    
    DB::table('menu_permissions')->delete();
    DB::table('menu_items')->delete();
    echo "✓ Existing menu data cleared\n";
    $operations[] = 'data_cleared';

    // Step 5: Run the updated menu seeder with error handling
    echo "\nStep 5: Running updated menu seeder...\n";
    
    // Verify seeder class exists
    if (!class_exists('Database\Seeders\MenuItemSeeder')) {
        throw new Exception("MenuItemSeeder class not found");
    }
    
    $seeder = new MenuItemSeeder();
    
    // Verify required models exist
    if (!class_exists('App\Models\MenuItem')) {
        throw new Exception("MenuItem model not found");
    }
    if (!class_exists('App\Models\MenuPermission')) {
        throw new Exception("MenuPermission model not found");
    }
    if (!class_exists('App\Models\Role')) {
        throw new Exception("Role model not found");
    }
    
    $seeder->run();
    echo "✓ Menu seeder completed\n";
    $operations[] = 'seeder_completed';

    // Step 6: Verify the results
    echo "\nStep 6: Verifying results...\n";
    $newMenuCount = DB::table('menu_items')->count();
    $newPermissionCount = DB::table('menu_permissions')->count();
    
    echo "New data: $newMenuCount menu items, $newPermissionCount permissions\n";
    
    if ($newMenuCount < 13) {
        throw new Exception("Expected at least 13 menu items, got $newMenuCount");
    }
    
    // Check for required menu items
    $requiredPaths = ['/', '/profile', '/cases', '/mediation', '/clients', '/finance/cash', '/calendar', '/users', '/documents', '/notifications', '/workflow', '/menu-management', '/search'];
    $existingPaths = DB::table('menu_items')->pluck('path')->toArray();
    
    foreach ($requiredPaths as $path) {
        if (!in_array($path, $existingPaths)) {
            throw new Exception("Required menu item missing: $path");
        }
    }
    
    // Check for mediation sub-items
    $mediationSubPaths = ['/mediation/list', '/mediation/new', '/arbitration', '/arbitration/dashboard'];
    foreach ($mediationSubPaths as $path) {
        if (!in_array($path, $existingPaths)) {
            throw new Exception("Required mediation sub-item missing: $path");
        }
    }
    
    echo "✓ All required menu items verified\n";
    $operations[] = 'verification_completed';

    echo "\n=== SUCCESS ===\n";
    echo "Menu structure update completed successfully!\n";
    echo "All mediation-related items are now grouped under 'Arabuluculuk' menu.\n";
    echo "Backup file: $backupFile\n";
    echo "\nMenu Structure:\n";
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

} catch (Exception $e) {
    echo "\n=== ERROR ===\n";
    echo "Error updating menu structure: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    // Attempt rollback if we have backup data
    if (!empty($rollbackData) && in_array('data_cleared', $operations)) {
        echo "\nAttempting rollback...\n";
        try {
            DB::table('menu_permissions')->delete();
            DB::table('menu_items')->delete();
            
            // Restore menu items
            foreach ($rollbackData['menu_items'] as $item) {
                DB::table('menu_items')->insert((array)$item);
            }
            
            // Restore permissions
            foreach ($rollbackData['menu_permissions'] as $permission) {
                DB::table('menu_permissions')->insert((array)$permission);
            }
            
            echo "✓ Rollback completed successfully\n";
        } catch (Exception $rollbackError) {
            echo "✗ Rollback failed: " . $rollbackError->getMessage() . "\n";
            echo "Manual restoration may be required from backup file: $backupFile\n";
        }
    }
    
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

// Function to restore from backup if needed
function restoreFromBackup($backupFile) {
    if (!file_exists($backupFile)) {
        throw new Exception("Backup file not found: $backupFile");
    }
    
    $backupData = json_decode(file_get_contents($backupFile), true);
    if (!$backupData) {
        throw new Exception("Invalid backup file format");
    }
    
    DB::table('menu_permissions')->delete();
    DB::table('menu_items')->delete();
    
    foreach ($backupData['menu_items'] as $item) {
        DB::table('menu_items')->insert($item);
    }
    
    foreach ($backupData['menu_permissions'] as $permission) {
        DB::table('menu_permissions')->insert($permission);
    }
    
    echo "✓ Menu restored from backup: $backupFile\n";
}