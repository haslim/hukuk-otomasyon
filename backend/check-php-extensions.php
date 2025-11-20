<?php

echo "=== PHP EXTENSIONS DIAGNOSTIC ===\n";
echo "Checking required PHP extensions for the menu system...\n\n";

// Check PHP version
echo "PHP Version: " . PHP_VERSION . "\n";
if (version_compare(PHP_VERSION, '8.0.0', '<')) {
    echo "⚠️  WARNING: PHP version should be 8.0 or higher for optimal compatibility\n";
} else {
    echo "✓ PHP version is compatible\n";
}
echo "\n";

// Required extensions for the menu system
$requiredExtensions = [
    'pdo' => 'PHP Data Objects (PDO) - Database abstraction layer',
    'pdo_mysql' => 'PDO MySQL driver - MySQL database connection',
    'mbstring' => 'Multibyte string functions - UTF-8 support',
    'json' => 'JSON functions - Data serialization',
    'openssl' => 'OpenSSL - Cryptographic functions',
    'curl' => 'cURL - HTTP client functionality',
    'fileinfo' => 'File Information - MIME type detection',
    'gd' => 'GD Graphics Library - Image processing',
    'xml' => 'XML parser - XML document handling',
    'tokenizer' => 'Tokenizer - PHP source parsing',
];

$optionalExtensions = [
    'redis' => 'Redis client - Caching support',
    'imagick' => 'ImageMagick - Advanced image processing',
    'zip' => 'Zip - Archive handling',
    'intl' => 'Internationalization - Locale support',
];

echo "=== REQUIRED EXTENSIONS ===\n";
$allRequiredLoaded = true;

foreach ($requiredExtensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✓' : '✗';
    echo "{$status} {$ext}: {$description}\n";
    
    if (!$loaded) {
        $allRequiredLoaded = false;
        
        // Provide installation instructions
        echo "   Installation instructions:\n";
        if (stripos(PHP_OS, 'WIN') === 0) {
            echo "   - Windows: Uncomment 'extension={$ext}' in php.ini\n";
        } else {
            echo "   - Ubuntu/Debian: sudo apt-get install php{$ext}\n";
            echo "   - CentOS/RHEL: sudo yum install php{$ext}\n";
            echo "   - Or: sudo phpenmod {$ext}\n";
        }
    } else {
        // Show version if available
        $version = phpversion($ext);
        if ($version) {
            echo "   Version: {$version}\n";
        }
    }
    echo "\n";
}

echo "=== OPTIONAL EXTENSIONS ===\n";
foreach ($optionalExtensions as $ext => $description) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? '✓' : '○';
    echo "{$status} {$ext}: {$description}\n";
    
    if ($loaded) {
        $version = phpversion($ext);
        if ($version) {
            echo "   Version: {$version}\n";
        }
    } else {
        echo "   (Optional - not required for basic functionality)\n";
    }
    echo "\n";
}

// Check specific configuration
echo "=== DATABASE CONFIGURATION ===\n";

// Check PDO drivers
$pdoDrivers = PDO::getAvailableDrivers();
echo "Available PDO drivers: " . implode(', ', $pdoDrivers) . "\n";

if (in_array('mysql', $pdoDrivers)) {
    echo "✓ MySQL PDO driver is available\n";
} else {
    echo "✗ MySQL PDO driver is NOT available\n";
    echo "   This is required for MySQL database connectivity\n";
    echo "   Installation: Install php-mysql package\n";
}

echo "\n";

// Check if we can connect to database (try without credentials first)
echo "=== DATABASE CONNECTIVITY TEST ===\n";
try {
    // Try to create a PDO instance to test basic functionality
    $testPdo = new PDO('mysql:host=localhost;dbname=test', 'test', 'test');
    echo "✓ PDO MySQL extension is functional\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "✓ PDO MySQL extension is functional (credentials error is expected)\n";
    } else {
        echo "⚠️  PDO MySQL extension may have issues: " . $e->getMessage() . "\n";
    }
} catch (Exception $e) {
    echo "✗ PDO MySQL extension test failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Check file permissions for critical files
echo "=== FILE PERMISSIONS CHECK ===\n";
$criticalFiles = [
    __DIR__ . '/bootstrap/app.php' => 'Bootstrap file',
    __DIR__ . '/config/database.php' => 'Database config',
    __DIR__ . '/.env' => 'Environment file',
];

foreach ($criticalFiles as $file => $description) {
    if (file_exists($file)) {
        $readable = is_readable($file);
        $status = $readable ? '✓' : '✗';
        echo "{$status} {$description}: " . basename($file) . "\n";
        
        if (!$readable) {
            echo "   Fix: chmod 644 {$file}\n";
        }
    } else {
        echo "○ {$description}: " . basename($file) . " (not found)\n";
    }
}

echo "\n";

// Memory and execution limits
echo "=== PHP CONFIGURATION ===\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n";
echo "Max execution time: " . ini_get('max_execution_time') . " seconds\n";
echo "Upload max filesize: " . ini_get('upload_max_filesize') . "\n";
echo "Post max size: " . ini_get('post_max_size') . "\n";

if ((int)ini_get('memory_limit') < 128) {
    echo "⚠️  Consider increasing memory_limit to 128M or higher\n";
}

if ((int)ini_get('max_execution_time') < 30) {
    echo "⚠️  Consider increasing max_execution_time to 30 or higher\n";
}

echo "\n";

// Summary
echo "=== SUMMARY ===\n";
if ($allRequiredLoaded) {
    echo "✓ All required PHP extensions are loaded!\n";
    echo "✓ Your PHP environment is ready for the menu system.\n";
} else {
    echo "✗ Some required PHP extensions are missing!\n";
    echo "Please install the missing extensions and restart your web server.\n";
}

echo "\n=== NEXT STEPS ===\n";
if ($allRequiredLoaded) {
    echo "1. Run the menu restoration script:\n";
    echo "   php improved-emergency-menu-restore.php\n\n";
    echo "2. Or run the fixed update script:\n";
    echo "   php fixed-update-menu-structure.php\n\n";
    echo "3. Test the menu system in your application.\n";
} else {
    echo "1. Install missing PHP extensions as shown above\n";
    echo "2. Restart your web server (Apache/Nginx)\n";
    echo "3. Run this diagnostic script again to verify\n";
    echo "4. Then proceed with menu restoration\n";
}

echo "\nDiagnostic completed.\n";