<?php
/**
 * Web-based Database Migration Runner
 * Use this only if you don't have terminal/SSH access to your hosting
 * 
 * SECURITY: This file should be removed after successful migration
 */

// Security check - allow only from specific IP or with a key
$allowedIPs = ['127.0.0.1', '::1']; // Add your IP here
$securityKey = $_GET['key'] ?? '';

if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIPs) && $securityKey !== 'change_this_to_a_secure_key') {
    http_response_code(403);
    die('Access denied');
}

// Set content type
header('Content-Type: text/plain; charset=utf-8');

echo "BGAofis Law Office Automation - Database Migration\n";
echo "==================================================\n\n";

try {
    // Load environment
    $basePath = dirname(__DIR__);
    require_once $basePath . '/vendor/autoload.php';
    
    if (file_exists($basePath . '/.env')) {
        Dotenv\Dotenv::createImmutable($basePath)->safeLoad();
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
            echo "✓ Done\n";
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
    
    echo "\nIMPORTANT: Delete this file (migrate.php) for security!\n";
    
} catch (Exception $e) {
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}