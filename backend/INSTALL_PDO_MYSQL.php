<?php
/**
 * Install PDO MySQL Extension for Windows PHP
 * This script helps download and configure the missing pdo_mysql extension
 */

echo "PDO MySQL Extension Installer for Windows\n";
echo "==========================================\n\n";

$phpVersion = PHP_VERSION;
$phpPath = dirname(PHP_BINARY);
$extDir = $phpPath . '/ext';
$phpIniPath = php_ini_loaded_file();

echo "PHP Version: $phpVersion\n";
echo "PHP Path: $phpPath\n";
echo "Extension Directory: $extDir\n";
echo "PHP INI: $phpIniPath\n\n";

// Check if we can write to the extension directory
if (!is_writable($extDir)) {
    echo "❌ Cannot write to extension directory: $extDir\n";
    echo "Please run this script as administrator or check permissions.\n";
    exit(1);
}

// Check if we can write to php.ini
if (!is_writable($phpIniPath)) {
    echo "❌ Cannot write to PHP INI file: $phpIniPath\n";
    echo "Please run this script as administrator or check permissions.\n";
    exit(1);
}

echo "✅ Write permissions OK\n\n";

// Download URL for PHP 8.2 extensions
$downloadUrl = "https://windows.php.net/downloads/releases/php-8.2.29-nts-Win32-vs16-x64.zip";
$extractPath = sys_get_temp_dir() . '/php_extensions_' . time();

echo "Downloading PHP 8.2.29 extensions package...\n";

// Create temporary directory
if (!mkdir($extractPath, 0777, true)) {
    echo "❌ Failed to create temporary directory\n";
    exit(1);
}

// Download the zip file
$zipFile = $extractPath . '/php.zip';
$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'PHP Extension Installer'
    ]
]);

if (!file_put_contents($zipFile, fopen($downloadUrl, 'rb', false, $context))) {
    echo "❌ Failed to download PHP extensions package\n";
    echo "Please download manually from: $downloadUrl\n";
    echo "And extract pdo_mysql.dll to: $extDir\n";
    rmdir($extractPath);
    exit(1);
}

echo "✅ Downloaded PHP extensions package\n";

// Extract the zip file
$zip = new ZipArchive();
if ($zip->open($zipFile) !== TRUE) {
    echo "❌ Failed to open downloaded zip file\n";
    unlink($zipFile);
    rmdir($extractPath);
    exit(1);
}

echo "Extracting extensions...\n";

// Look for pdo_mysql.dll in the zip
$pdoMysqlFound = false;
for ($i = 0; $i < $zip->numFiles; $i++) {
    $filename = $zip->getNameIndex($i);
    if (basename($filename) === 'pdo_mysql.dll') {
        echo "Found pdo_mysql.dll in: $filename\n";
        
        // Extract the file
        $zip->extractTo($extractPath, [$filename]);
        
        // Move it to the extension directory
        $sourceFile = $extractPath . '/' . $filename;
        $destFile = $extDir . '/pdo_mysql.dll';
        
        if (rename($sourceFile, $destFile)) {
            echo "✅ Copied pdo_mysql.dll to: $destFile\n";
            $pdoMysqlFound = true;
        } else {
            echo "❌ Failed to copy pdo_mysql.dll\n";
        }
        break;
    }
}

$zip->close();
unlink($zipFile);

// Clean up
function removeDirectory($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
    rmdir($dir);
}
removeDirectory($extractPath);

if (!$pdoMysqlFound) {
    echo "❌ pdo_mysql.dll not found in the downloaded package\n";
    echo "Please download manually and extract pdo_mysql.dll to: $extDir\n";
    exit(1);
}

echo "\n";

// Update php.ini
echo "Updating php.ini...\n";

$iniContent = file_get_contents($phpIniPath);
if (strpos($iniContent, 'extension=pdo_mysql') === false) {
    // Find the extension section
    $extensionPattern = '/(\n;?\s*extension\s*=\s*[a-z_]+\s*\n)/';
    if (preg_match($extensionPattern, $iniContent, $matches)) {
        // Add after the first extension line
        $iniContent = str_replace($matches[1], $matches[1] . "\nextension=pdo_mysql\n", $iniContent);
    } else {
        // Add at the end
        $iniContent .= "\n; Added by PDO MySQL Installer\nextension=pdo_mysql\n";
    }
    
    if (file_put_contents($phpIniPath, $iniContent)) {
        echo "✅ Added extension=pdo_mysql to php.ini\n";
    } else {
        echo "❌ Failed to update php.ini\n";
        echo "Please manually add 'extension=pdo_mysql' to: $phpIniPath\n";
        exit(1);
    }
} else {
    echo "✅ extension=pdo_mysql already found in php.ini\n";
}

echo "\n";
echo "✅ Installation completed!\n\n";

echo "Next steps:\n";
echo "1. Restart your web server (Apache/Nginx/IIS)\n";
echo "2. Restart your terminal/command prompt\n";
echo "3. Run: php WINDOWS_PHP_MYSQL_FIX.php to verify\n";
echo "4. Run: php quick-fix.php to test database connection\n\n";

echo "If you're using this PHP installation for web development,\n";
echo "make sure to restart your web server to load the new extension.\n\n";

// Test if extension is now available (may require restart)
echo "Testing extension availability...\n";
if (dl('pdo_mysql')) {
    echo "✅ PDO MySQL extension loaded successfully!\n";
} else {
    echo "⚠️  Extension installed but requires server restart to load\n";
}