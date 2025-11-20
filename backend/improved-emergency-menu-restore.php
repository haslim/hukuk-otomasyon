<?php

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Str;

echo "=== IMPROVED EMERGENCY MENU RESTORATION ===\n";
echo "Restoring complete menu system with all items and permissions...\n\n";

try {
    // Step 1: Verify database connection and tables
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

    // Step 2: Check current menu state
    echo "\nStep 2: Checking current menu state...\n";
    $menuCount = DB::table('menu_items')->count();
    $permissionCount = DB::table('menu_permissions')->count();
    
    echo "Current state:\n";
    echo "- Menu items: $menuCount\n";
    echo "- Menu permissions: $permissionCount\n";
    
    if ($menuCount > 0) {
        echo "WARNING: Menu data already exists. Clearing existing data...\n";
        DB::table('menu_permissions')->delete();
        DB::table('menu_items')->delete();
        echo "✓ Existing menu data cleared\n";
    }

    // Step 3: Create all menu items with proper hierarchy
    echo "\nStep 3: Creating menu items...\n";
    
    // Use transaction to ensure data integrity
    DB::transaction(function () {
        // Main menu items (13 items)
        $mainMenuItems = [
            ['path' => '/', 'label' => 'Dashboard', 'icon' => 'dashboard', 'sort_order' => 1],
            ['path' => '/profile', 'label' => 'Profilim', 'icon' => 'account_circle', 'sort_order' => 2],
            ['path' => '/cases', 'label' => 'Dosyalar', 'icon' => 'folder', 'sort_order' => 3],
            ['path' => '/mediation', 'label' => 'Arabuluculuk', 'icon' => 'handshake', 'sort_order' => 4],
            ['path' => '/clients', 'label' => 'Müvekkiller', 'icon' => 'group', 'sort_order' => 5],
            ['path' => '/finance/cash', 'label' => 'Kasa', 'icon' => 'account_balance_wallet', 'sort_order' => 6],
            ['path' => '/calendar', 'label' => 'Takvim', 'icon' => 'calendar_month', 'sort_order' => 7],
            ['path' => '/users', 'label' => 'Kullanıcılar & Roller', 'icon' => 'manage_accounts', 'sort_order' => 8],
            ['path' => '/documents', 'label' => 'Dokümanlar', 'icon' => 'folder_open', 'sort_order' => 9],
            ['path' => '/notifications', 'label' => 'Bildirimler', 'icon' => 'notifications', 'sort_order' => 10],
            ['path' => '/workflow', 'label' => 'Workflow', 'icon' => 'route', 'sort_order' => 11],
            ['path' => '/menu-management', 'label' => 'Menü Yönetimi', 'icon' => 'menu', 'sort_order' => 12],
            ['path' => '/search', 'label' => 'Arama', 'icon' => 'search', 'sort_order' => 13],
        ];

        $createdMenuItems = [];
        
        // Create main menu items
        foreach ($mainMenuItems as $item) {
            $id = Str::uuid();
            $menuItem = [
                'id' => $id,
                'path' => $item['path'],
                'label' => $item['label'],
                'icon' => $item['icon'],
                'sort_order' => $item['sort_order'],
                'is_active' => true,
                'parent_id' => null,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            
            DB::table('menu_items')->insert($menuItem);
            $createdMenuItems[$item['path']] = (object) array_merge($menuItem, ['id' => $id]);
            echo "✓ Created: {$item['label']}\n";
        }

        // Create Arabuluculuk sub-menu items (4 items)
        echo "\nCreating Arabuluculuk sub-menu items...\n";
        $mediationParent = $createdMenuItems['/mediation'];
        $mediationSubItems = [
            ['path' => '/mediation/list', 'label' => 'Arabuluculuk Dosyaları', 'icon' => 'list', 'sort_order' => 1],
            ['path' => '/mediation/new', 'label' => 'Yeni Arabuluculuk Başvurusu', 'icon' => 'add', 'sort_order' => 2],
            ['path' => '/arbitration', 'label' => 'Arabuluculuk Başvuruları', 'icon' => 'gavel', 'sort_order' => 3],
            ['path' => '/arbitration/dashboard', 'label' => 'Arabuluculuk İstatistikleri', 'icon' => 'bar_chart', 'sort_order' => 4],
        ];

        foreach ($mediationSubItems as $item) {
            $id = Str::uuid();
            $subMenuItem = [
                'id' => $id,
                'path' => $item['path'],
                'label' => $item['label'],
                'icon' => $item['icon'],
                'sort_order' => $item['sort_order'],
                'is_active' => true,
                'parent_id' => $mediationParent->id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ];
            
            DB::table('menu_items')->insert($subMenuItem);
            $createdMenuItems[$item['path']] = (object) array_merge($subMenuItem, ['id' => $id]);
            echo "✓ Created sub-menu: {$item['label']}\n";
        }

        // Step 4: Create role-based permissions
        echo "\nStep 4: Creating role-based permissions...\n";
        
        // Get roles
        $adminRole = DB::table('roles')->where('key', 'administrator')->first();
        $lawyerRole = DB::table('roles')->where('key', 'lawyer')->first();
        
        if (!$adminRole) {
            echo "WARNING: Administrator role not found. Creating it...\n";
            $adminRoleId = Str::uuid();
            DB::table('roles')->insert([
                'id' => $adminRoleId,
                'key' => 'administrator',
                'name' => 'Administrator',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
            $adminRole = (object) ['id' => $adminRoleId];
            echo "✓ Created administrator role\n";
        }
        
        if (!$lawyerRole) {
            echo "WARNING: Lawyer role not found. Creating it...\n";
            $lawyerRoleId = Str::uuid();
            DB::table('roles')->insert([
                'id' => $lawyerRoleId,
                'key' => 'lawyer',
                'name' => 'Avukat',
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
            $lawyerRole = (object) ['id' => $lawyerRoleId];
            echo "✓ Created lawyer role\n";
        }

        // Administrator permissions - all menus visible
        echo "Creating administrator permissions (all menus)...\n";
        foreach ($createdMenuItems as $menuItem) {
            DB::table('menu_permissions')->insert([
                'id' => Str::uuid(),
                'role_id' => $adminRole->id,
                'menu_item_id' => $menuItem->id,
                'is_visible' => true,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
        echo "✓ Administrator permissions created\n";

        // Lawyer permissions - restricted menus
        echo "Creating lawyer permissions (restricted menus)...\n";
        $visibleForLawyer = [
            '/', // Dashboard
            '/cases', // Dosyalar
            '/mediation', // Arabuluculuk (ana menü)
            '/mediation/list', // Arabuluculuk Dosyaları
            '/mediation/new', // Yeni Arabuluculuk Başvurusu
            '/arbitration', // Arabuluculuk Başvuruları
            '/arbitration/dashboard', // Arabuluculuk İstatistikleri
            '/clients', // Müvekkiller
            '/finance/cash', // Kasa
            '/calendar', // Takvim
            '/documents', // Dokümanlar
            '/notifications', // Bildirimler
            '/search', // Arama
        ];

        foreach ($createdMenuItems as $path => $menuItem) {
            $isVisible = in_array($path, $visibleForLawyer);
            DB::table('menu_permissions')->insert([
                'id' => Str::uuid(),
                'role_id' => $lawyerRole->id,
                'menu_item_id' => $menuItem->id,
                'is_visible' => $isVisible,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
        echo "✓ Lawyer permissions created\n";
    });

    // Step 5: Verify restoration
    echo "\nStep 5: Verifying restoration...\n";
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

    echo "\n✓ Emergency menu restoration completed successfully!\n";
    echo "The application menu navigation should now be fully functional.\n";
    echo "Total menu items: $newMenuCount\n";
    echo "Total permissions: $newPermissionCount\n";

} catch (Exception $e) {
    echo "\n=== ERROR ===\n";
    echo "ERROR during emergency restoration: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
