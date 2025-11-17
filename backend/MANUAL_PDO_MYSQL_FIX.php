<?php
/**
 * Manual PDO MySQL Extension Fix for Windows
 * Provides step-by-step instructions for installing the missing extension
 */

echo "Manual PDO MySQL Extension Fix for Windows\n";
echo "==========================================\n\n";

$phpVersion = PHP_VERSION;
$phpPath = dirname(PHP_BINARY);
$extDir = $phpPath . '/ext';
$phpIniPath = php_ini_loaded_file();

echo "PHP Version: $phpVersion\n";
echo "PHP Path: $phpPath\n";
echo "Extension Directory: $extDir\n";
echo "PHP INI: $phpIniPath\n\n";

echo "STEP-BY-STEP SOLUTION:\n";
echo "======================\n\n";

echo "STEP 1: Download PHP 8.2.29 with extensions\n";
echo "--------------------------------------------\n";
echo "1. Go to: https://windows.php.net/downloads/releases/archives/\n";
echo "2. Download: php-8.2.29-nts-Win32-vs16-x64.zip\n";
echo "3. Extract the zip file to a temporary folder\n\n";

echo "STEP 2: Copy the PDO MySQL extension\n";
echo "-----------------------------------\n";
echo "1. Navigate to the extracted folder\n";
echo "2. Go to the 'ext' subfolder\n";
echo "3. Find 'pdo_mysql.dll'\n";
echo "4. Copy it to: $extDir\n\n";

echo "STEP 3: Enable the extension in php.ini\n";
echo "----------------------------------------\n";
echo "1. Open: $phpIniPath\n";
echo "2. Find the extensions section (look for ;extension=...)\n";
echo "3. Add this line: extension=pdo_mysql\n";
echo "4. Save the file\n\n";

echo "STEP 4: Restart services\n";
echo "-----------------------\n";
echo "1. Restart your web server (Apache/Nginx/IIS)\n";
echo "2. Restart your terminal/command prompt\n";
echo "3. Test with: php WINDOWS_PHP_MYSQL_FIX.php\n\n";

echo "ALTERNATIVE: Install XAMPP (Recommended)\n";
echo "=========================================\n";
echo "1. Download XAMPP from: https://www.apachefriends.org/\n";
echo "2. Install XAMPP with Apache + MySQL + PHP\n";
echo "3. Use the PHP that comes with XAMPP (usually C:/xampp/php/)\n";
echo "4. Update your system PATH to use XAMPP's PHP\n";
echo "5. XAMPP includes all necessary extensions by default\n\n";

echo "QUICK TEST COMMANDS:\n";
echo "====================\n";
echo "After fixing, run these commands to verify:\n\n";
echo "1. Check extension:\n";
echo "   php -m | findstr pdo_mysql\n\n";
echo "2. Run diagnostic:\n";
echo "   cd backend\n";
echo "   php WINDOWS_PHP_MYSQL_FIX.php\n\n";
echo "3. Test database:\n";
echo "   php quick-fix.php\n\n";

echo "CURRENT STATUS:\n";
echo "===============\n";
echo "PDO: " . (extension_loaded('pdo') ? "✅ LOADED" : "❌ NOT LOADED") . "\n";
echo "PDO MySQL: " . (extension_loaded('pdo_mysql') ? "✅ LOADED" : "❌ NOT LOADED") . "\n";
echo "MySQL Native Driver: " . (extension_loaded('mysqlnd') ? "✅ LOADED" : "❌ NOT LOADED") . "\n\n";

if (extension_loaded('pdo')) {
    echo "Available PDO Drivers:\n";
    $drivers = PDO::getAvailableDrivers();
    if (empty($drivers)) {
        echo "❌ No PDO drivers available\n";
    } else {
        foreach ($drivers as $driver) {
            echo "✅ " . $driver . "\n";
        }
    }
}

echo "\n";
echo "TROUBLESHOOTING:\n";
echo "================\n";
echo "If you still get 'could not find driver' error:\n";
echo "1. Make sure pdo_mysql.dll is in: $extDir\n";
echo "2. Check that extension=pdo_mysql is uncommented in php.ini\n";
echo "3. Restart ALL services (web server, terminal, IDE)\n";
echo "4. Check for multiple PHP installations on your system\n";
echo "5. Use 'where php' to find which PHP is being used\n\n";

echo "FILE LOCATIONS TO CHECK:\n";
echo "=======================\n";
echo "PHP executable: " . PHP_BINARY . "\n";
echo "PHP INI file: " . php_ini_loaded_file() . "\n";
echo "Extension dir: " . ini_get('extension_dir') . "\n\n";

echo "After completing the manual installation, run:\n";
echo "php quick-fix.php\n";
echo "to test the database connection and create the admin user.\n\n";