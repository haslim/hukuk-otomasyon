<?php
/**
 * Test Menu System with Eloquent
 */

echo "Testing Menu System with Eloquent\n";
echo "=================================\n\n";

try {
    // Load environment
    $basePath = __DIR__;
    require_once $basePath . '/vendor/autoload.php';

    if (file_exists($basePath . '/.env')) {
        Dotenv\Dotenv::createImmutable($basePath)->safeLoad();
    }

    // Initialize Eloquent
    $capsule = new Illuminate\Database\Capsule\Manager();
    $capsule->addConnection([
        'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'database' => $_ENV['DB_DATABASE'] ?? 'bgaofis',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => ''
    ]);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    echo "✓ Eloquent initialized successfully\n";

    // Test 1: Check if tables exist
    echo "\n--- Table Existence Check ---\n";
    try {
        $menuItemsCount = $capsule->table('menu_items')->count();
        echo "menu_items table: EXISTS (Count: $menuItemsCount)\n";
    } catch (Exception $e) {
        echo "menu_items table: MISSING - " . $e->getMessage() . "\n";
    }

    try {
        $menuPermsCount = $capsule->table('menu_permissions')->count();
        echo "menu_permissions table: EXISTS (Count: $menuPermsCount)\n";
    } catch (Exception $e) {
        echo "menu_permissions table: MISSING - " . $e->getMessage() . "\n";
    }

    // Test 2: Check data if tables exist
    echo "\n--- Data Verification ---\n";
    try {
        $menuItems = $capsule->table('menu_items')
            ->orderBy('sort_order')
            ->get(['path', 'label', 'sort_order']);
        
        echo "All Menu Items:\n";
        foreach ($menuItems as $item) {
            echo "  {$item->sort_order}. {$item->label} ({$item->path})\n";
        }

        // Test 3: Role-based menu check
        echo "\n--- Role Menu Assignments ---\n";
        $roles = $capsule->table('roles')->get(['id', 'name', 'key']);
        
        foreach ($roles as $role) {
            $visibleCount = $capsule->table('menu_permissions')
                ->where('role_id', $role->id)
                ->where('is_visible', 1)
                ->count();
            
            echo "Role: {$role->name} - Visible menus: $visibleCount\n";
        }

        // Test 4: Lawyer specific check
        echo "\n--- Lawyer Role Menu Details ---\n";
        $lawyerRole = $capsule->table('roles')
            ->where('key', 'lawyer')
            ->first();

        if ($lawyerRole) {
            $lawyerMenus = $capsule->table('menu_permissions')
                ->join('menu_items', 'menu_permissions.menu_item_id', '=', 'menu_items.id')
                ->where('menu_permissions.role_id', $lawyerRole->id)
                ->orderBy('menu_items.sort_order')
                ->get([
                    'menu_items.path',
                    'menu_items.label', 
                    'menu_permissions.is_visible'
                ]);

            $restrictedCount = 0;
            echo "Lawyer Role Menu Access:\n";
            foreach ($lawyerMenus as $menu) {
                $status = $menu->is_visible ? 'VISIBLE' : 'HIDDEN';
                echo "  {$menu->path} - {$menu->label} - $status\n";
                if (!$menu->is_visible) $restrictedCount++;
            }
            echo "Total restricted menus for lawyer: $restrictedCount\n";
        }

    } catch (Exception $e) {
        echo "Data verification failed: " . $e->getMessage() . "\n";
    }

    echo "\n✓ Menu system test completed!\n";

} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
