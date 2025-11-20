<?php

/**
 * Debug .env File Loading
 * Helps diagnose why environment variables are not loading
 */

echo "=== .env File Loading Debug ===\n\n";

$envPath = dirname(__DIR__);
echo "Working directory: " . __DIR__ . "\n";
echo "Environment path: $envPath\n";
echo ".env file path: $envPath/.env\n\n";

// Check if .env file exists
if (file_exists($envPath . '/.env')) {
    echo "✓ .env file exists\n";
    echo "File size: " . filesize($envPath . '/.env') . " bytes\n";
    echo "File readable: " . (is_readable($envPath . '/.env') ? 'YES' : 'NO') . "\n";
    
    // Show first few lines of .env file (without sensitive data)
    $lines = file($envPath . '/.env');
    echo "\nFirst 10 lines of .env:\n";
    for ($i = 0; $i < min(10, count($lines)); $i++) {
        $line = trim($lines[$i]);
        // Hide passwords
        if (strpos($line, 'PASSWORD') !== false) {
            $line = preg_replace('/=.*/', '=***HIDDEN***', $line);
        }
        if (strpos($line, 'JWT_SECRET') !== false) {
            $line = preg_replace('/=.*/', '=***HIDDEN***', $line);
        }
        echo "  Line " . ($i + 1) . ": $line\n";
    }
} else {
    echo "✗ .env file NOT found\n";
    echo "Creating sample .env file...\n";
    
    $sampleEnv = "# Application Configuration
APP_NAME=BGAofis
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bgaofis.billurguleraslim.av.tr

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=haslim_bgofis
DB_USERNAME=haslim_bgofis
DB_PASSWORD=Fener1907****

# JWT / Auth Configuration
JWT_SECRET=7x9K2mN5pQ8rT3wV6yZ1aB4cD7eF0gH3jK5lM8nO2pS5uV8yX1bC4dE7fG0hJ3kL6
";
    
    file_put_contents($envPath . '/.env', $sampleEnv);
    echo "✓ Sample .env file created with your actual values\n";
}

// Test different dotenv loading methods
echo "\n=== Testing Dotenv Loading ===\n";

try {
    // Method 1: Basic loading
    echo "Testing method 1: Basic dotenv loading...\n";
    $dotenv = Dotenv::createImmutable($envPath);
    $dotenv->load();
    echo "  ✓ Basic loading successful\n";
    echo "  APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";
} catch (Exception $e) {
    echo "  ✗ Basic loading failed: " . $e->getMessage() . "\n";
}

// Reset environment
$_ENV = [];

try {
    // Method 2: Safe loading
    echo "Testing method 2: Safe dotenv loading...\n";
    $dotenv = Dotenv::createImmutable($envPath)->safeLoad();
    echo "  ✓ Safe loading successful\n";
    echo "  APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";
} catch (Exception $e) {
    echo "  ✗ Safe loading failed: " . $e->getMessage() . "\n";
}

// Reset environment
$_ENV = [];

try {
    // Method 3: With specific file
    echo "Testing method 3: Specific file loading...\n";
    $dotenv = Dotenv::createImmutable($envPath, '.env');
    $dotenv->load();
    echo "  ✓ Specific file loading successful\n";
    echo "  APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";
} catch (Exception $e) {
    echo "  ✗ Specific file loading failed: " . $e->getMessage() . "\n";
}

echo "\n=== Manual Environment Test ===\n";

// Try reading file manually and setting $_ENV manually
if (file_exists($envPath . '/.env')) {
    echo "Reading .env file manually...\n";
    $content = file_get_contents($envPath . '/.env');
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
    
    echo "✓ Manual parsing completed\n";
    echo "  APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . "\n";
    echo "  DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
    echo "  DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
    echo "  DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'not set') . "\n";
    echo "  DB_PASSWORD: " . (empty($_ENV['DB_PASSWORD']) ? 'EMPTY' : 'SET') . "\n";
    echo "  JWT_SECRET: " . (empty($_ENV['JWT_SECRET']) ? 'NOT SET' : 'SET') . "\n";
}

echo "\n=== Recommendations ===\n";
echo "1. Ensure .env file is in the correct directory: $envPath\n";
echo "2. Check file permissions (should be 644)\n";
echo "3. Verify dotenv library is installed\n";
echo "4. Try manual environment setting as shown above\n";

echo "\n=== Debug Complete ===\n";
