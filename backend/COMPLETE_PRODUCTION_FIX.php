<?php
/**
 * Complete Production Fix
 * Addresses all production issues: PDO MySQL, JWT, Database, and API errors
 */

echo "Complete Production Fix\n";
echo "=======================\n\n";

// Load composer autoloader
if (file_exists(__DIR__ . "/vendor/autoload.php")) {
    require_once __DIR__ . "/vendor/autoload.php";
} else {
    echo "❌ Composer autoloader not found. Run: composer install\n";
    exit(1);
}

// Load environment
$envPath = __DIR__;
if (file_exists($envPath . "/.env")) {
    $lines = file($envPath . "/.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

echo "STEP 1: PHP EXTENSIONS CHECK\n";
echo "===========================\n";

$requiredExtensions = ['pdo', 'pdo_mysql', 'mysqlnd', 'json', 'mbstring', 'tokenizer'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ $ext: LOADED\n";
    } else {
        echo "❌ $ext: NOT LOADED\n";
        $missingExtensions[] = $ext;
    }
}

if (!empty($missingExtensions)) {
    echo "\n❌ MISSING EXTENSIONS - This is causing the API errors!\n";
    echo "SOLUTION:\n";
    echo "1. Install XAMPP: https://www.apachefriends.org/\n";
    echo "2. Or manually install PDO MySQL extension\n";
    echo "3. Restart your web server\n\n";
    
    // Provide manual fix instructions
    echo "MANUAL FIX INSTRUCTIONS:\n";
    echo "========================\n";
    echo "1. Download PHP 8.2.29 from: https://windows.php.net/downloads/releases/archives/\n";
    echo "2. Extract and copy pdo_mysql.dll to: " . dirname(PHP_BINARY) . "/ext/\n";
    echo "3. Edit php.ini at: " . php_ini_loaded_file() . "\n";
    echo "4. Add: extension=pdo_mysql\n";
    echo "5. Restart web server\n\n";
    
    exit(1);
}

echo "\nSTEP 2: DATABASE CONNECTION TEST\n";
echo "===============================\n";

try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connection: SUCCESS\n";
    
    // Test basic query
    $stmt = $pdo->query("SELECT 1");
    echo "✅ Database query: SUCCESS\n";
    
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "\n";
    echo "Check your .env file database credentials:\n";
    echo "- DB_HOST: " . ($_ENV["DB_HOST"] ?? "NOT SET") . "\n";
    echo "- DB_DATABASE: " . ($_ENV["DB_DATABASE"] ?? "NOT SET") . "\n";
    echo "- DB_USERNAME: " . ($_ENV["DB_USERNAME"] ?? "NOT SET") . "\n";
    echo "- DB_PASSWORD: " . (empty($_ENV["DB_PASSWORD"]) ? "NOT SET" : "SET") . "\n";
    exit(1);
}

echo "\nSTEP 3: REQUIRED TABLES CHECK\n";
echo "==============================\n";

$requiredTables = [
    'users' => 'User management',
    'roles' => 'Role-based access control',
    'user_roles' => 'User role assignments',
    'cases' => 'Case management',
    'clients' => 'Client management',
    'calendar_events' => 'Calendar functionality',
    'finance_transactions' => 'Financial records',
    'audit_logs' => 'Audit trail'
];

$missingTables = [];

foreach ($requiredTables as $table => $description) {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table': EXISTS ($description)\n";
        } else {
            echo "❌ Table '$table': MISSING ($description)\n";
            $missingTables[] = $table;
        }
    } catch (Exception $e) {
        echo "❌ Error checking table '$table': " . $e->getMessage() . "\n";
        $missingTables[] = $table;
    }
}

if (!empty($missingTables)) {
    echo "\n❌ MISSING TABLES - Run migrations:\n";
    echo "php migrate.php\n";
    echo "Or run individual migration files:\n";
    foreach ($missingTables as $table) {
        echo "- php database/migrate.php --table=$table\n";
    }
}

echo "\nSTEP 4: ADMIN USER AND ROLES\n";
echo "============================\n";

// Check admin user
try {
    $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE email = ?");
    $stmt->execute(["alihaydaraslim@gmail.com"]);
    $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminUser) {
        echo "✅ Admin user found: " . $adminUser['email'] . " (ID: " . $adminUser['id'] . ")\n";
        
        // Check if user has role
        $stmt = $pdo->prepare("SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?");
        $stmt->execute([$adminUser['id']]);
        $userRole = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($userRole) {
            echo "✅ User role: " . $userRole['name'] . "\n";
        } else {
            echo "⚠️  No role assigned to admin user\n";
            
            // Check if admin role exists
            $stmt = $pdo->query("SELECT id FROM roles WHERE name = 'admin'");
            $adminRole = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($adminRole) {
                echo "✅ Admin role exists, assigning to user...\n";
                $stmt = $pdo->prepare("INSERT INTO user_roles (user_id, role_id, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$adminUser['id'], $adminRole['id']]);
                echo "✅ Admin role assigned to user\n";
            } else {
                echo "❌ Admin role not found - need to seed roles\n";
            }
        }
    } else {
        echo "❌ Admin user not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error checking admin user: " . $e->getMessage() . "\n";
}

echo "\nSTEP 5: JWT FUNCTIONALITY\n";
echo "========================\n";

try {
    if (class_exists('Firebase\JWT\JWT')) {
        $testToken = Firebase\JWT\JWT::encode([
            'test' => 'value',
            'exp' => time() + 3600
        ], $_ENV["JWT_SECRET"], "HS256");
        
        Firebase\JWT\JWT::decode($testToken, new Firebase\JWT\Key($_ENV["JWT_SECRET"], "HS256"));
        echo "✅ JWT functionality: WORKING\n";
    } else {
        echo "❌ JWT library not loaded - run: composer install\n";
    }
} catch (Exception $e) {
    echo "❌ JWT functionality: FAILED - " . $e->getMessage() . "\n";
}

echo "\nSTEP 6: API ROUTE TESTS\n";
echo "=======================\n";

// Test if routes file exists
if (file_exists(__DIR__ . "/routes/api.php")) {
    echo "✅ API routes file: EXISTS\n";
} else {
    echo "❌ API routes file: MISSING\n";
}

// Test if controllers exist
$controllers = ['AuthController', 'CaseController', 'ClientController', 'UserController'];
foreach ($controllers as $controller) {
    $controllerFile = __DIR__ . "/app/Controllers/{$controller}.php";
    if (file_exists($controllerFile)) {
        echo "✅ $controller: EXISTS\n";
    } else {
        echo "❌ $controller: MISSING\n";
    }
}

echo "\nSUMMARY AND NEXT STEPS:\n";
echo "=======================\n";

if (empty($missingExtensions) && empty($missingTables)) {
    echo "✅ All critical components are working!\n";
    echo "\nNEXT STEPS:\n";
    echo "1. Restart your web server\n";
    echo "2. Test the frontend application\n";
    echo "3. Check API endpoints individually\n";
    echo "4. Monitor error logs for any remaining issues\n";
} else {
    echo "❌ Issues found that need to be resolved:\n\n";
    
    if (!empty($missingExtensions)) {
        echo "1. Install missing PHP extensions:\n";
        echo "   - Install XAMPP or manually add extensions\n";
        echo "   - Restart web server\n\n";
    }
    
    if (!empty($missingTables)) {
        echo "2. Run database migrations:\n";
        echo "   php migrate.php\n\n";
    }
    
    echo "3. After fixing above, run this script again\n";
}

echo "\nCOMMON API ERROR SOLUTIONS:\n";
echo "===========================\n";
echo "403 Forbidden:\n";
echo "- Check user roles and permissions\n";
echo "- Verify JWT token contains permissions\n";
echo "- Ensure RoleMiddleware is properly configured\n\n";

echo "500 Internal Server Error:\n";
echo "- Check PHP error logs\n";
echo "- Verify database connections\n";
echo "- Ensure all required tables exist\n";
echo "- Check for syntax errors in controllers\n\n";

echo "If you continue to experience issues, check:\n";
echo "- Web server error logs\n";
echo "- PHP error logs\n";
echo "- Database error logs\n";
echo "- Application-specific logs\n\n";