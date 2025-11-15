<?php
/**
 * Database Migration Runner for Production
 * This script will create the missing finance_transactions table and other tables
 */

echo "BGAofis Law Office Automation - Database Migration\n";
echo "==================================================\n\n";

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
    echo "Database: " . ($_ENV['DB_DATABASE'] ?? 'bgaofis') . "\n";
    echo "Host: " . ($_ENV['DB_HOST'] ?? '127.0.0.1') . "\n\n";

    // Run migrations
    $migrationsPath = $basePath . '/database/migrations';
    $files = glob($migrationsPath . '/*.php');
    sort($files);

    echo "Running migrations:\n";
    echo "------------------\n";

    foreach ($files as $file) {
        $migration = require $file;
        if (is_object($migration) && method_exists($migration, 'up')) {
            echo "Running: " . basename($file) . "... ";
            $migration->up();
            echo "Done\n";
        }
    }

    echo "\nMigration completed successfully!\n";

    // Optional: Run seeders
    if (isset($argv[1]) && $argv[1] === 'seed') {
        echo "\nRunning seeders:\n";
        echo "----------------\n";

        $seederPath = $basePath . '/database/seed.php';
        if (file_exists($seederPath)) {
            require $seederPath;
            echo "Seeders executed.\n";
        } else {
            echo "No seeders found.\n";
        }
    }

    echo "\nAll database tables have been created successfully!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
