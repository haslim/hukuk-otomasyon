<?php

require_once __DIR__ . '/bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as DB;
use Database\Seeders\MenuItemSeeder;

echo "Updating menu structure for mediation grouping...\n";

try {
    // Run the migration for menu hierarchy
    echo "Running menu hierarchy migration...\n";
    $migration = new class {
        public function up() {
            DB::schema()->table('menu_items', function ($table) {
                if (!DB::schema()->hasColumn('menu_items', 'parent_id')) {
                    $table->uuid('parent_id')->nullable()->after('id');
                    $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('cascade');
                    $table->index('parent_id');
                }
            });
        }
    };
    
    $migration->up();
    echo "✓ Menu hierarchy migration completed\n";

    // Clear existing menu items and permissions
    echo "Clearing existing menu data...\n";
    DB::table('menu_permissions')->delete();
    DB::table('menu_items')->delete();
    echo "✓ Existing menu data cleared\n";

    // Run the updated menu seeder
    echo "Running updated menu seeder...\n";
    $seeder = new MenuItemSeeder();
    $seeder->run();
    echo "✓ Menu seeder completed\n";

    echo "\nMenu structure update completed successfully!\n";
    echo "All mediation-related items are now grouped under 'Arabuluculuk' menu.\n";

} catch (Exception $e) {
    echo "Error updating menu structure: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}