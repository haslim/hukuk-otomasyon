<?php
/**
 * BGAofis Law Office Automation - Deployment Script
 * This script helps with the initial deployment setup
 */

echo "BGAofis Law Office Automation - Deployment Setup\n";
echo "================================================\n";

// Check if .env file exists
if (!file_exists(__DIR__ . '/.env')) {
    echo "ERROR: .env file not found!\n";
    echo "Please copy .env.example to .env and configure it with your production values.\n";
    exit(1);
}

// Check if vendor directory exists
if (!is_dir(__DIR__ . '/vendor')) {
    echo "ERROR: vendor directory not found!\n";
    echo "Please run 'composer install --no-dev --optimize-autoloader' in backend directory.\n";
    exit(1);
}

// Create necessary directories
$directories = [
    __DIR__ . '/logs',
    __DIR__ . '/uploads',
    __DIR__ . '/temp'
];

echo "Creating necessary directories...\n";
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✓ Created directory: " . basename($dir) . "\n";
        } else {
            echo "✗ Failed to create directory: " . basename($dir) . "\n";
        }
    } else {
        echo "✓ Directory already exists: " . basename($dir) . "\n";
    }
}

// Check database connection
echo "\nChecking database connection...\n";
try {
    require_once __DIR__ . '/vendor/autoload.php';
    
    if (file_exists(__DIR__ . '/.env')) {
        Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();
    }
    
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
    
    $connection = $capsule->getConnection();
    $connection->getPdo();
    echo "✓ Database connection successful\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in .env file\n";
}

// Check file permissions
echo "\nChecking file permissions...\n";
$filesToCheck = [
    '.env' => 0644,
    'logs' => 0755,
    'uploads' => 0755,
    'backups' => 0755
];

foreach ($filesToCheck as $file => $expectedPerm) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $currentPerm = substr(sprintf('%o', fileperms($path)), -4);
        if ($currentPerm >= $expectedPerm) {
            echo "✓ Permissions OK for $file: $currentPerm\n";
        } else {
            echo "⚠ Consider setting permissions for $file to: " . decoct($expectedPerm) . "\n";
        }
    }
}

echo "\nDeployment setup check completed!\n";
echo "Next steps:\n";
echo "1. Run database migrations: php database/migrate.php\n";
echo "2. If needed, run database seeders: php database/seed.php\n";
echo "3. Test your API endpoints\n";
echo "4. If needed, set up cron jobs for scheduled tasks (if any)\n";