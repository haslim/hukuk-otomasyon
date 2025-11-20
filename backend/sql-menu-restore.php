<?php

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Str;

echo "=== SQL-BASED MENU RESTORATION ===\n";
echo "Restoring menu system using direct SQL queries...\n\n";

// Set higher time limit
set_time_limit(300); // 5 minutes

try {
    // Step 1: Verify database connection
    echo "Step 1: Verifying database connection...\n";
    if (!DB::connection()->getPdo()) {
        throw new Exception("Database connection failed");
    }
    echo "✓ Database connection verified\n";

    // Check if menu tables exist
    $tables = DB::select("SHOW TABLES LIKE 'menu_%'");
    if (empty($tables)) {
        echo "ERROR: Menu tables not found. Please run migrations first.\n";
        exit(1);
    }
    echo "✓ Menu tables found\n";

    // Step 2: Clear existing data
    echo "\nStep 2: Clearing existing menu data...\n";
    DB::statement("SET FOREIGN_KEY_CHECKS = 0");
    DB::table('menu_permissions')->delete();
    DB::table('menu_items')->delete();
    DB::statement("SET FOREIGN_KEY_CHECKS = 1");
    echo "✓ Existing menu data cleared\n";

    // Step 3: Get or create roles
    echo "\nStep 3: Verifying roles...\n";
    $adminRole = DB::table('roles')->where('key', 'administrator')->first();
    $lawyerRole = DB::table('roles')->where('key', 'lawyer')->first();
    
    if (!$adminRole) {
        echo "Creating administrator role...\n";
        $adminRoleId = Str::uuid();
        DB::statement("INSERT INTO roles (id, `key`, name, created_at, updated_at) VALUES (?, 'administrator', 'Administrator', NOW(), NOW())", [$adminRoleId]);
        $adminRole = (object) ['id' => $adminRoleId];
    }
    
    if (!$lawyerRole) {
        echo "Creating lawyer role...\n";
        $lawyerRoleId = Str::uuid();
        DB::statement("INSERT INTO roles (id, `key`, name, created_at, updated_at) VALUES (?, 'lawyer', 'Avukat', NOW(), NOW())", [$lawyerRoleId]);
        $lawyerRole = (object) ['id' => $lawyerRoleId];
    }
    echo "✓ Roles verified\n";

    // Step 4: Create menu items using raw SQL
    echo "\nStep 4: Creating menu items using SQL...\n";
    
    // Generate UUIDs for all menu items
    $menuUuids = [];
    $menuPaths = [
        '/', '/profile', '/cases', '/mediation', '/clients', 
        '/finance/cash', '/calendar', '/users', '/documents', 
        '/notifications', '/workflow', '/menu-management', '/search',
        '/mediation/list', '/mediation/new', '/arbitration', '/arbitration/dashboard'
    ];
    
    foreach ($menuPaths as $path) {
        $menuUuids[$path] = Str::uuid();
    }
    
    // Main menu items
    $mainMenuSql = "
        INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
        (?, '/', 'Dashboard', 'dashboard', 1, 1, NULL, NOW(), NOW()),
        (?, '/profile', 'Profilim', 'account_circle', 2, 1, NULL, NOW(), NOW()),
        (?, '/cases', 'Dosyalar', 'folder', 3, 1, NULL, NOW(), NOW()),
        (?, '/mediation', 'Arabuluculuk', 'handshake', 4, 1, NULL, NOW(), NOW()),
        (?, '/clients', 'Müvekkiller', 'group', 5, 1, NULL, NOW(), NOW()),
        (?, '/finance/cash', 'Kasa', 'account_balance_wallet', 6, 1, NULL, NOW(), NOW()),
        (?, '/calendar', 'Takvim', 'calendar_month', 7, 1, NULL, NOW(), NOW()),
        (?, '/users', 'Kullanıcılar & Roller', 'manage_accounts', 8, 1, NULL, NOW(), NOW()),
        (?, '/documents', 'Dokümanlar', 'folder_open', 9, 1, NULL, NOW(), NOW()),
        (?, '/notifications', 'Bildirimler', 'notifications', 10, 1, NULL, NOW(), NOW()),
        (?, '/workflow', 'Workflow', 'route', 11, 1, NULL, NOW(), NOW()),
        (?, '/menu-management', 'Menü Yönetimi', 'menu', 12, 1, NULL, NOW(), NOW()),
        (?, '/search', 'Arama', 'search', 13, 1, NULL, NOW(), NOW())
    ";
    
    $mainMenuParams = [
        $menuUuids['/'], $menuUuids['/profile'], $menuUuids['/cases'], $menuUuids['/mediation'],
        $menuUuids['/clients'], $menuUuids['/finance/cash'], $menuUuids['/calendar'], $menuUuids['/users'],
        $menuUuids['/documents'], $menuUuids['/notifications'], $menuUuids['/workflow'],
        $menuUuids['/menu-management'], $menuUuids['/search']
    ];
    
    DB::statement($mainMenuSql, $mainMenuParams);
    echo "✓ Main menu items created\n";
    
    // Sub-menu items
    $subMenuSql = "
        INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
        (?, '/mediation/list', 'Arabuluculuk Dosyaları', 'list', 1, 1, ?, NOW(), NOW()),
        (?, '/mediation/new', 'Yeni Arabuluculuk Başvurusu', 'add', 2, 1, ?, NOW(), NOW()),
        (?, '/arbitration', 'Arabuluculuk Başvuruları', 'gavel', 3, 1, ?, NOW(), NOW()),
        (?, '/arbitration/dashboard', 'Arabuluculuk İstatistikleri', 'bar_chart', 4, 1, ?, NOW(), NOW())
    ";
    
    $subMenuParams = [
        $menuUuids['/mediation/list'], $menuUuids['/mediation'],
        $menuUuids['/mediation/new'], $menuUuids['/mediation'],
        $menuUuids['/arbitration'], $menuUuids['/mediation'],
        $menuUuids['/arbitration/dashboard'], $menuUuids['/mediation']
    ];
    
    DB::statement($subMenuSql, $subMenuParams);
    echo "✓ Sub-menu items created\n";

    // Step 5: Create permissions using raw SQL
    echo "\nStep 5: Creating permissions using SQL...\n";
    
    // Administrator permissions (all menus)
    $adminPermissions = [];
    foreach ($menuPaths as $path) {
        $adminPermissions[] = "(UUID(), '{$adminRole->id}', '{$menuUuids[$path]}', 1, NOW(), NOW())";
    }
    $adminPermissionsSql = "INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at) VALUES " . implode(',', $adminPermissions);
    DB::statement($adminPermissionsSql);
    echo "✓ Administrator permissions created\n";
    
    // Lawyer permissions (restricted)
    $visibleForLawyer = [
        '/', '/cases', '/mediation', '/mediation/list', '/mediation/new',
        '/arbitration', '/arbitration/dashboard', '/clients', '/finance/cash',
        '/calendar', '/documents', '/notifications', '/search'
    ];
    
    $lawyerPermissions = [];
    foreach ($menuPaths as $path) {
        $isVisible = in_array($path, $visibleForLawyer) ? 1 : 0;
        $lawyerPermissions[] = "(UUID(), '{$lawyerRole->id}', '{$menuUuids[$path]}', $isVisible, NOW(), NOW())";
    }
    $lawyerPermissionsSql = "INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at) VALUES " . implode(',', $lawyerPermissions);
    DB::statement($lawyerPermissionsSql);
    echo "✓ Lawyer permissions created\n";

    // Step 6: Verify restoration
    echo "\nStep 6: Verifying restoration...\n";
    $newMenuCount = DB::table('menu_items')->count();
    $newPermissionCount = DB::table('menu_permissions')->count();
    
    echo "Restoration complete!\n";
    echo "- Menu items created: $newMenuCount (expected: 17)\n";
    echo "- Menu permissions created: $newPermissionCount\n";
    
    if ($newMenuCount != 17) {
        throw new Exception("Expected 17 menu items, got $newMenuCount");
    }

    // Display menu structure
    echo "\n=== MENU STRUCTURE ===\n";
    echo "Main Menu Items (13):\n";
    echo "=====================\n";
    
    $mainMenus = DB::table('menu_items')
        ->whereNull('parent_id')
        ->orderBy('sort_order')
        ->get();
    
    foreach ($mainMenus as $menu) {
        echo "• {$menu->label} ({$menu->path})\n";
        
        // Show sub-items for Arabuluculuk
        if ($menu->path === '/mediation') {
            $subItems = DB::table('menu_items')
                ->where('parent_id', $menu->id)
                ->orderBy('sort_order')
                ->get();
            
            foreach ($subItems as $subItem) {
                echo "  └─ {$subItem->label} ({$subItem->path})\n";
            }
        }
    }

    echo "\n=== ROLE PERMISSIONS ===\n";
    echo "Administrator: All 17 menu items\n";
    echo "Lawyer: 13 menu items (restricted access)\n";

    echo "\n✓ SQL-based menu restoration completed successfully!\n";
    echo "The application menu navigation should now be fully functional.\n";
    echo "Total menu items: $newMenuCount\n";
    echo "Total permissions: $newPermissionCount\n";

} catch (Exception $e) {
    echo "\n=== ERROR ===\n";
    echo "ERROR during restoration: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}