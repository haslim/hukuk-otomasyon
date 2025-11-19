<?php
/**
 * Menu Tables Migration Runner
 */

echo "BGAofis Menu Tables Migration\n";
echo "==============================\n\n";

try {
    // Load environment
    $basePath = __DIR__;
    require_once $basePath . '/vendor/autoload.php';

    if (file_exists($basePath . '/.env')) {
        Dotenv\Dotenv::createImmutable($basePath)->safeLoad();
    } elseif (file_exists($basePath . '/.env.production')) {
        Dotenv\Dotenv::createImmutable($basePath, '.env.production')->safeLoad();
    }

    // Initialize database
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

    echo "Database connection established successfully.\n";
    echo "Database: " . $_ENV['DB_DATABASE'] . "\n\n";

    // Run menu migration
    $migrationFile = $basePath . '/database/migrations/2024_01_05_000000_create_menu_tables.php';
    if (file_exists($migrationFile)) {
        echo "Running menu tables migration:\n";
        $migration = require $migrationFile;
        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
            echo "Menu tables migration completed successfully!\n\n";
        }
    }

    // Run menu seeder
    $seederFile = $basePath . '/database/seeders/MenuItemSeeder.php';
    if (file_exists($seederFile)) {
        echo "Running menu seeder:\n";
        $seeder = new \Database\Seeders\MenuItemSeeder();
        $seeder->run();
        echo "Menu seeder completed successfully!\n\n";
    }

    echo "Menu system setup completed!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
