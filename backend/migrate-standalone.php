<?php
/**
 * Standalone Database Migration Runner
 * This script bypasses the Slim framework to run migrations directly
 */

// Set content type
header('Content-Type: text/plain; charset=utf-8');

echo "BGAofis Law Office Automation - Database Migration\n";
echo "==================================================\n\n";

// Security check - accept from both GET and command line
$securityKey = $_GET['key'] ?? ($argv[1] ?? '');
if ($securityKey !== 'bgaofis2024migration') {
    if (php_sapi_name() === 'cli') {
        die("Access denied - Invalid security key\nUsage: php migrate-standalone.php bgaofis2024migration\n");
    } else {
        http_response_code(403);
        die('Access denied - Invalid security key');
    }
}

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

    // Set up Schema facade properly
    $container = new Illuminate\Container\Container();
    $container->singleton('db', function () use ($capsule) {
        return $capsule->getDatabaseManager();
    });
    $container->singleton('db.schema', function () use ($capsule) {
        return $capsule->getDatabaseManager()->connection()->getSchemaBuilder();
    });
    Illuminate\Support\Facades\Schema::setFacadeApplication($container);

    echo "Database connection established successfully.\n";
    echo "Database: " . $_ENV['DB_DATABASE'] . "\n";
    echo "Host: " . $_ENV['DB_HOST'] . "\n\n";

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
    if (isset($_GET['seed']) && $_GET['seed'] === 'true') {
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
    echo "IMPORTANT: Delete this file (migrate-standalone.php) for security!\n";

} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
