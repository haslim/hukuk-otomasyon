<?php
/**
 * Production API Diagnostic
 * Analyzes and provides solutions for production API errors
 */

echo "Production API Diagnostic\n";
echo "=========================\n\n";

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

echo "ANALYZING API ERRORS:\n";
echo "====================\n\n";

echo "ERROR PATTERNS IDENTIFIED:\n";
echo "1. 403 Forbidden errors on: /api/cases, /api/finance/*\n";
echo "2. 500 Internal Server Error on: /api/clients, /api/calendar/events, /api/roles\n";
echo "3. 500 Internal Server Error on: /api/auth/logout\n\n";

echo "ROOT CAUSE ANALYSIS:\n";
echo "===================\n\n";

echo "403 Forbidden Causes:\n";
echo "- Missing or incorrect permissions in JWT token\n";
echo "- Role-based access control (RBAC) misconfiguration\n";
echo "- Missing required permissions for specific endpoints\n";
echo "- Database permission issues for certain tables\n\n";

echo "500 Internal Server Error Causes:\n";
echo "- Database connection issues (PDO MySQL)\n";
echo "- Missing database tables or migrations\n";
echo "- PHP syntax errors in controllers\n";
echo "- Missing dependencies or extensions\n";
echo "- Database foreign key constraint violations\n\n";

echo "DIAGNOSTIC TESTS:\n";
echo "================\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection:\n";
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"]
    );
    echo "✅ Database connection: SUCCESS\n";
    
    // Test key tables
    $tables = ['users', 'cases', 'clients', 'roles', 'calendar_events', 'finance_transactions'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Table '$table': EXISTS\n";
        } else {
            echo "❌ Table '$table': MISSING\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Database connection: FAILED - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: JWT Token Analysis
echo "2. Analyzing JWT Token:\n";
$sampleToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJiZ2FvZmlzIiwic3ViIjoyMiwianRpIjoiZmFiYzE0NTItZWE5YS00YTNmLWFiOTYtYTIyZTkyMDZiNDY4IiwiZXhwIjoxNzYzMzc5MjYwLCJwZXJtaXNzaW9ucyI6W119";
try {
    $decoded = Firebase\JWT\JWT::decode($sampleToken, new Firebase\JWT\Key($_ENV["JWT_SECRET"], "HS256"));
    echo "✅ JWT Token: VALID\n";
    echo "   - User ID: " . $decoded->sub . "\n";
    echo "   - Expires: " . date('Y-m-d H:i:s', $decoded->exp) . "\n";
    echo "   - Permissions: " . (empty($decoded->permissions) ? "EMPTY" : json_encode($decoded->permissions)) . "\n";
    
    if (empty($decoded->permissions)) {
        echo "⚠️  WARNING: Empty permissions array - this may cause 403 errors\n";
    }
} catch (Exception $e) {
    echo "❌ JWT Token: INVALID - " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: User Role Check
echo "3. Checking User Roles and Permissions:\n";
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"]
    );
    
    // Check user with ID 22 (from JWT)
    $stmt = $pdo->prepare("SELECT u.id, u.email, u.name, r.name as role_name FROM users u LEFT JOIN user_roles ur ON u.id = ur.user_id LEFT JOIN roles r ON ur.role_id = r.id WHERE u.id = ?");
    $stmt->execute([22]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User Found:\n";
        echo "   - ID: " . $user['id'] . "\n";
        echo "   - Email: " . $user['email'] . "\n";
        echo "   - Name: " . $user['name'] . "\n";
        echo "   - Role: " . ($user['role_name'] ?: 'NO ROLE ASSIGNED') . "\n";
        
        if (!$user['role_name']) {
            echo "⚠️  WARNING: No role assigned - this may cause permission issues\n";
        }
    } else {
        echo "❌ User with ID 22 not found\n";
    }
    
    // Check roles table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM roles");
    $roleCount = $stmt->fetch(PDO::FETCH_ASSOC)["count"];
    echo "   - Total roles in database: $roleCount\n";
    
    if ($roleCount === 0) {
        echo "❌ No roles found in database - need to seed roles\n";
    }
    
} catch (Exception $e) {
    echo "❌ Role check failed: " . $e->getMessage() . "\n";
}
echo "\n";

echo "SOLUTIONS:\n";
echo "==========\n\n";

echo "IMMEDIATE FIXES:\n";
echo "1. Run database migrations:\n";
echo "   php migrate.php\n\n";

echo "2. Seed roles and permissions:\n";
echo "   php database/seed.php\n\n";

echo "3. Assign role to admin user:\n";
echo "   UPDATE users SET role = 'admin' WHERE email = 'alihaydaraslim@gmail.com';\n\n";

echo "4. Check .env configuration:\n";
echo "   - Ensure DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD are correct\n";
echo "   - Ensure JWT_SECRET is set and matches frontend\n\n";

echo "5. Check file permissions on server:\n";
echo "   - Ensure PHP can write to log files\n";
echo "   - Check .htaccess configuration\n\n";

echo "TROUBLESHOOTING STEPS:\n";
echo "====================\n\n";

echo "For 403 Forbidden errors:\n";
echo "1. Verify JWT token contains proper permissions\n";
echo "2. Check user has required role for specific endpoints\n";
echo "3. Verify RoleMiddleware is correctly implemented\n";
echo "4. Check database role assignments\n\n";

echo "For 500 Internal Server Error:\n";
echo "1. Check PHP error logs: /var/log/php_errors.log or similar\n";
echo "2. Verify all database tables exist and are properly structured\n";
echo "3. Check for missing PHP extensions (pdo_mysql, etc.)\n";
echo "4. Verify .env file is correctly configured\n\n";

echo "QUICK TEST COMMANDS:\n";
echo "===================\n";
echo "1. Test database: php WINDOWS_PHP_MYSQL_FIX.php\n";
echo "2. Test authentication: php quick-fix.php\n";
echo "3. Run migrations: php migrate.php\n";
echo "4. Seed data: php database/seed.php\n\n";

echo "NEXT STEPS:\n";
echo "===========\n";
echo "1. Fix any database connection issues first\n";
echo "2. Run all pending migrations\n";
echo "3. Seed roles and permissions\n";
echo "4. Assign proper roles to users\n";
echo "5. Test API endpoints individually\n";
echo "6. Check server logs for specific error details\n\n";

echo "If issues persist, check the production server's:\n";
echo "- PHP error logs\n";
echo "- Apache/Nginx error logs\n";
echo "- MySQL error logs\n";
echo "- Application logs\n\n";