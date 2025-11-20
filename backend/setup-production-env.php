<?php

/**
 * Production Environment Setup Script
 * This script helps set up the .env file for production deployment
 */

echo "=== Production Environment Setup ===\n\n";

$envFile = __DIR__ . '/.env';
$envExample = __DIR__ . '/.env.example';

if (file_exists($envFile)) {
    echo "⚠ .env file already exists\n";
    echo "Current content:\n";
    echo file_get_contents($envFile) . "\n";
    echo "\nDo you want to overwrite it? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 'y') {
        echo "Setup cancelled.\n";
        exit(0);
    }
}

echo "Setting up production environment...\n\n";

// Generate secure JWT secret
function generateJwtSecret() {
    return bin2hex(random_bytes(32));
}

// Read example file
$exampleContent = file_get_contents($envExample);

// Replace with production values
$productionContent = str_replace([
    'APP_ENV=production',
    'DB_HOST=localhost',
    'DB_DATABASE=bgaofis',
    'DB_USERNAME=bgaofis_user',
    'DB_PASSWORD=strong_password_here',
    'JWT_SECRET=Z1lKTGVhVTZjeFh6bDZlRk45yQmJvV3FpbVFhYUmV5dE9PZE9sdUxSa2F3cGd3NjhRTHdEeU5wVUV5aU9JRa3BvNzkyWkpYZkhKT09=',
], [
    'APP_ENV=production',
    'DB_HOST=127.0.0.1',  // Update this with your production DB host
    'DB_DATABASE=bgaofis',  // Update this with your production DB name
    'DB_USERNAME=root',       // Update this with your production DB username
    'DB_PASSWORD=',          // Update this with your production DB password
    'JWT_SECRET=' . generateJwtSecret(),
], $exampleContent);

// Write production env file
file_put_contents($envFile, $productionContent);

echo "✓ Production .env file created\n";
echo "⚠ IMPORTANT: Please update the following values in $envFile:\n\n";
echo "1. DB_HOST - Your production database host\n";
echo "2. DB_DATABASE - Your production database name\n";
echo "3. DB_USERNAME - Your production database username\n";
echo "4. DB_PASSWORD - Your production database password\n";
echo "5. APP_URL - Your production application URL\n";
echo "6. Mail configuration settings\n";
echo "7. File upload paths\n\n";

echo "Current JWT Secret: " . generateJwtSecret() . "\n\n";

echo "=== Manual .env Template ===\n";
echo "Copy this template and update with your production values:\n\n";
echo $productionContent;

echo "\n=== Setup Complete ===\n";
echo "Next steps:\n";
echo "1. Update .env file with your production values\n";
echo "2. Run database migrations: php database/migrate.php\n";
echo "3. Run database seeds: php database/seed.php\n";
echo "4. Test authentication: php test-auth-database.php\n";
echo "5. Deploy CORS fix: php deploy-cors-fix-production.php\n";
