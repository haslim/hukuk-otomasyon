<?php
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
use Dotenv\Dotenv;
$envPath = __DIR__;
if (file_exists($envPath . '/.env')) {
    Dotenv::createImmutable($envPath)->safeLoad();
    echo "✅ Loaded .env file\n";
} else {
    echo "❌ .env file not found\n";
}

echo "=== Environment Validation ===\n";

$required = ["JWT_SECRET", "DB_HOST", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD"];
$missing = [];

foreach ($required as $key) {
    $value = $_ENV[$key] ?? null;
    if (empty($value)) {
        $missing[] = $key;
    } else {
        echo "✅ $key: " . substr($value, 0, 20) . "...\n";
    }
}

if (!empty($missing)) {
    echo "❌ Missing environment variables: " . implode(", ", $missing) . "\n";
    echo "Please check your .env file\n";
} else {
    echo "✅ All required environment variables are set\n";
}

// Check PHP extensions
$requiredExtensions = ["pdo", "pdo_mysql", "openssl"];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ PHP extension $ext is loaded\n";
    } else {
        echo "❌ PHP extension $ext is NOT loaded\n";
    }
}
