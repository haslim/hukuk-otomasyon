<?php

echo "=== DATABASE DIAGNOSTIC ===\n";

echo "Checking PHP extensions...\n";
$extensions = ['pdo', 'pdo_mysql', 'mysqli', 'mysqlnd'];
foreach ($extensions as $ext) {
    $loaded = extension_loaded($ext);
    echo "- $ext: " . ($loaded ? 'LOADED' : 'NOT LOADED') . "\n";
}

echo "\nChecking PDO drivers...\n";
if (extension_loaded('pdo')) {
    $drivers = PDO::getAvailableDrivers();
    echo "Available PDO drivers: " . implode(', ', $drivers) . "\n";
    
    if (in_array('mysql', $drivers)) {
        echo "✓ MySQL PDO driver is available\n";
    } else {
        echo "✗ MySQL PDO driver is NOT available\n";
    }
} else {
    echo "✗ PDO extension is not loaded\n";
}

echo "\nTesting database connection...\n";
try {
    // Try direct PDO connection
    $dsn = "mysql:host=localhost;dbname=haslim_bgofis;charset=utf8mb4";
    $pdo = new PDO($dsn, 'haslim_bgofis', 'Fener1907****');
    echo "✓ Direct PDO connection successful\n";
    $pdo = null;
} catch (PDOException $e) {
    echo "✗ Direct PDO connection failed: " . $e->getMessage() . "\n";
}

echo "\nChecking Laravel Eloquent...\n";
try {
    require_once __DIR__ . '/bootstrap/app.php';
    echo "✓ Bootstrap loaded successfully\n";
    
    if (class_exists('Illuminate\Database\Capsule\Manager')) {
        echo "✓ Eloquent Capsule class exists\n";
        
        try {
            $connection = \Illuminate\Database\Capsule\Manager::connection();
            $pdo = $connection->getPdo();
            echo "✓ Eloquent connection successful\n";
        } catch (Exception $e) {
            echo "✗ Eloquent connection failed: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ Eloquent Capsule class not found\n";
    }
} catch (Exception $e) {
    echo "✗ Bootstrap failed: " . $e->getMessage() . "\n";
}

echo "\nDiagnostic complete.\n";