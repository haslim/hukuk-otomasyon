<?php
/**
 * BGAofis Law Office Automation - Comprehensive Diagnostic
 * This script diagnoses all potential issues causing API failures
 * Designed to identify root causes of 500, 403, and 405 errors
 */

echo "╔══════════════════════════════════════════════════╗\n";
echo "║           BGAOFIS COMPREHENSIVE DIAGNOSTIC TOOL           ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "📋 Loading environment variables from .env...\n";
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !empty(trim($line)) && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            $_SERVER[trim($key)] = trim($value);
        }
    }
    echo "✓ Environment variables loaded\n";
} else {
    echo "⚠ .env file not found, using defaults\n";
}

echo "\n1. 🔍 TESTING DATABASE CONNECTION AND SCHEMA\n";
echo str_repeat("─", 60) . "\n";

try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_DATABASE'] ?? 'haslim_bgofis';
    $username = $_ENV['DB_USERNAME'] ?? 'haslim_bgofis';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful\n";
    
    // Check audit_logs table structure
    echo "📊 Checking audit_logs table structure...\n";
    $columns = $pdo->query("SHOW COLUMNS FROM audit_logs")->fetchAll();
    $issues = [];
    
    foreach ($columns as $column) {
        $fieldName = $column['Field'];
        $fieldType = $column['Type'];
        $fieldNull = $column['Null'];
        $fieldKey = $column['Key'];
        
        echo "  Column: {$fieldName} - Type: {$fieldType}, Null: {$fieldNull}, Key: {$fieldKey}\n";
        
        // Check for specific issues
        if ($fieldName === 'entity_id' && strpos($fieldType, 'varchar(36)') === false) {
            $issues[] = "❌ entity_id column is not VARCHAR(36) - UUID truncation will occur";
        }
        
        if ($fieldName === 'user_id' && strpos($fieldType, 'varchar(36)') === false) {
            $issues[] = "❌ user_id column is not VARCHAR(36) - foreign key issues may occur";
        }
        
        if ($fieldName === 'id' && strpos($fieldType, 'varchar(36)') === false) {
            $issues[] = "❌ id column is not VARCHAR(36) - UUID issues may occur";
        }
    }
    
    if (empty($issues)) {
        echo "✅ audit_logs table structure appears correct\n";
    } else {
        echo "❌ audit_logs table issues found:\n";
        foreach ($issues as $issue) {
            echo "   {$issue}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n2. 🔍 TESTING REQUIRED CONTROLLERS\n";
echo str_repeat("─", 60) . "\n";

$requiredControllers = [
    'AuthController' => 'backend/app/Controllers/AuthController.php',
    'ClientController' => 'backend/app/Controllers/ClientController.php',
    'CaseController' => 'backend/app/Controllers/CaseController.php',
    'FinanceController' => 'backend/app/Controllers/FinanceController.php',
    'CalendarController' => 'backend/app/Controllers/CalendarController.php',
    'UserController' => 'backend/app/Controllers/UserController.php',
    'NotificationController' => 'backend/app/Controllers/NotificationController.php',
    'WorkflowController' => 'backend/app/Controllers/WorkflowController.php',
    'DashboardController' => 'backend/app/Controllers/DashboardController.php',
    'ProfileController' => 'backend/app/Controllers/ProfileController.php',
    'TaskController' => 'backend/app/Controllers/TaskController.php',
    'DocumentController' => 'backend/app/Controllers/DocumentController.php',
    'SearchController' => 'backend/app/Controllers/SearchController.php'
];

foreach ($requiredControllers as $controller => $path) {
    if (file_exists($path)) {
        echo "✅ {$controller}: EXISTS\n";
    } else {
        echo "❌ {$controller}: MISSING at {$path}\n";
    }
}

echo "\n3. 🔍 TESTING MIDDLEWARE\n";
echo str_repeat("─", 60) . "\n";

$requiredMiddleware = [
    'AuthMiddleware' => 'backend/app/Middleware/AuthMiddleware.php',
    'AuditLogMiddleware' => 'backend/app/Middleware/AuditLogMiddleware.php',
    'RoleMiddleware' => 'backend/app/Middleware/RoleMiddleware.php'
];

foreach ($requiredMiddleware as $middleware => $path) {
    if (file_exists($path)) {
        echo "✅ {$middleware}: EXISTS\n";
    } else {
        echo "❌ {$middleware}: MISSING at {$path}\n";
    }
}

echo "\n4. 🔍 TESTING ROUTES FILE\n";
echo str_repeat("─", 60) . "\n";

$routesFile = __DIR__ . '/routes/api.php';
if (file_exists($routesFile)) {
    echo "✅ Routes file exists: {$routesFile}\n";
    
    $routesContent = file_get_contents($routesFile);
    if ($routesContent !== false) {
        // Check for specific missing routes
        $missingRoutes = [];
        
        if (strpos($routesContent, 'POST /api/auth/register') === false) {
            $missingRoutes[] = 'POST /api/auth/register';
        }
        
        if (strpos($routesContent, 'GET /api/roles') === false) {
            $missingRoutes[] = 'GET /api/roles';
        }
        
        if (strpos($routesContent, 'POST /api/users') === false) {
            $missingRoutes[] = 'POST /api/users';
        }
        
        if (strpos($routesContent, 'PUT /api/users/{id}') === false) {
            $missingRoutes[] = 'PUT /api/users/{id}';
        }
        
        if (strpos($routesContent, 'DELETE /api/users/{id}') === false) {
            $missingRoutes[] = 'DELETE /api/users/{id}';
        }
        
        if (strpos($routesContent, 'GET /api/finance/cash-stats') === false) {
            $missingRoutes[] = 'GET /api/finance/cash-stats';
        }
        
        if (strpos($routesContent, 'GET /api/finance/cash-transactions') === false) {
            $missingRoutes[] = 'GET /api/finance/cash-transactions';
        }
        
        if (strpos($routesContent, 'POST /api/finance/cash-transactions') === false) {
            $missingRoutes[] = 'POST /api/finance/cash-transactions';
        }
        
        if (strpos($routesContent, 'GET /api/calendar/events') === false) {
            $missingRoutes[] = 'GET /api/calendar/events';
        }
        
        if (strpos($routesContent, 'POST /api/calendar/events') === false) {
            $missingRoutes[] = 'POST /api/calendar/events';
        }
        
        if (strpos($routesContent, 'PUT /api/calendar/events/{id}') === false) {
            $missingRoutes[] = 'PUT /api/calendar/events/{id}';
        }
        
        if (strpos($routesContent, 'DELETE /api/calendar/events/{id}') === false) {
            $missingRoutes[] = 'DELETE /api/calendar/events/{id}';
        }
        
        if (strpos($routesContent, 'GET /api/notifications') === false) {
            $missingRoutes[] = 'GET /api/notifications';
        }
        
        if (strpos($routesContent, 'POST /api/notifications/dispatch') === false) {
            $missingRoutes[] = 'POST /api/notifications/dispatch';
        }
        
        if (empty($missingRoutes)) {
            echo "✅ All required routes appear to be present\n";
        } else {
            echo "❌ Missing routes detected:\n";
            foreach ($missingRoutes as $route) {
                echo "   - {$route}\n";
            }
        }
    }
} else {
    echo "❌ Routes file NOT FOUND\n";
}

echo "\n5. 🔍 TESTING FILE PERMISSIONS\n";
echo str_repeat("─", 60) . "\n";

$criticalFiles = [
    '.env' => '0644',
    'routes/api.php' => '0644',
    'vendor/autoload.php' => '0755',
    'public/index.php' => '0644',
    'app/Controllers/' => '0755',
    'app/Middleware/' => '0755'
];

foreach ($criticalFiles as $file => $expectedPerm) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        $actualPerm = substr(sprintf('%o', fileperms($fullPath)), -4);
        if ($actualPerm >= $expectedPerm) {
            echo "✅ {$file}: Permissions OK ({$actualPerm})\n";
        } else {
            echo "❌ {$file}: Permissions ISSUE ({$actualPerm}, expected {$expectedPerm})\n";
        }
    } else {
        echo "❌ {$file}: FILE NOT FOUND\n";
    }
}

echo "\n6. 🔍 TESTING AUTHENTICATION SYSTEM\n";
echo str_repeat("─", 60) . "\n";

try {
    // Test if we can create a JWT token (basic auth test)
    echo "🔐 Testing JWT token generation...\n";
    
    // Check if users table exists and has proper structure
    $usersTable = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (!empty($usersTable)) {
        echo "✅ Users table exists\n";
        
        $userColumns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll();
        $hasUuid = false;
        $hasPassword = false;
        
        foreach ($userColumns as $column) {
            if ($column['Field'] === 'id' && strpos($column['Type'], 'varchar') !== false) {
                $hasUuid = true;
            }
            if ($column['Field'] === 'password') {
                $hasPassword = true;
            }
        }
        
        echo "  Users table structure:\n";
        echo "    - UUID ID column: " . ($hasUuid ? "✅" : "❌") . "\n";
        echo "    - Password column: " . ($hasPassword ? "✅" : "❌") . "\n";
    } else {
        echo "❌ Users table NOT FOUND\n";
    }
    
} catch (Exception $e) {
    echo "❌ Authentication test failed: " . $e->getMessage() . "\n";
}

echo "\n7. 🔍 TESTING SPECIFIC ERROR SCENARIOS\n";
echo str_repeat("─", 60) . "\n";

try {
    echo "🧪 Testing specific error scenarios...\n";
    
    // Test 1: Try to insert a problematic UUID
    echo "  Test 1: UUID insertion test...\n";
    $testUuid = '75ea5c9c-ea28-4f4a-bd17-fcb47d4660bc';
    $stmt = $pdo->prepare("INSERT INTO audit_logs (id, entity_type, entity_id, action, metadata, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        'test-' . uniqid(),
        'client',
        $testUuid,
        'TEST',
        json_encode(['test' => true])
    ]);
    echo "  ✅ UUID insertion test completed\n";
    
    // Clean up
    $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE id = ?");
    $stmt->execute(['test-' . uniqid()]);
    
} catch (Exception $e) {
    echo "  ❌ UUID insertion test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 DIAGNOSTIC SUMMARY\n";
echo str_repeat("=", 60) . "\n";

echo "📊 SYSTEM STATUS:\n";
echo "   Database Connection: " . (isset($pdo) ? "✅ CONNECTED" : "❌ FAILED") . "\n";
echo "   Audit Logs Schema: " . (empty($issues) ? "✅ CORRECT" : "❌ NEEDS FIX") . "\n";
echo "   Controllers: " . (count(array_filter($requiredControllers, 'file_exists')) === count($requiredControllers) ? "✅ ALL PRESENT" : "❌ SOME MISSING") . "\n";
echo "   Middleware: " . (count(array_filter($requiredMiddleware, 'file_exists')) === count($requiredMiddleware) ? "✅ ALL PRESENT" : "❌ SOME MISSING") . "\n";
echo "   Routes File: " . (file_exists($routesFile) ? "✅ EXISTS" : "❌ MISSING") . "\n";
echo "   File Permissions: " . (count(array_filter($criticalFiles, function($filename) { return file_exists(__DIR__ . '/' . $filename); }) === count($criticalFiles) ? "✅ CHECKED" : "❌ ISSUES") . "\n";

echo "\n🔧 RECOMMENDED ACTIONS:\n";
if (!empty($issues) || !file_exists($routesFile) || count(array_filter($requiredControllers, 'file_exists')) < count($requiredControllers)) {
    echo "   1. 🚀 RUN: php fix-missing-routes.php\n";
    echo "   2. 🔍 CHECK: All controllers and middleware files exist\n";
    echo "   3. 📋 VERIFY: Routes file contains all required endpoints\n";
    echo "   4. 🧪 TEST: All API endpoints with proper authentication\n";
} else {
    echo "   ✅ SYSTEM APPEARS HEALTHY\n";
    echo "   🧪 TEST: Individual API endpoints to identify specific issues\n";
    echo "   📊 MONITOR: Check application logs for recurring errors\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎯 NEXT STEPS:\n";
echo "1. If issues found above, run the recommended fixes\n";
echo "2. If system appears healthy, test specific failing endpoints\n";
echo "3. Check server error logs for detailed error messages\n";
echo "4. Verify .env configuration matches database setup\n";
echo "5. Ensure all required files have correct permissions\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🔍 TROUBLESHOOTING SPECIFIC ERRORS:\n";
echo "   500 Errors: Usually database/PHP issues\n";
echo "   403 Errors: Usually authentication/permission issues\n";
echo "   405 Errors: Usually missing routes/HTTP method issues\n";
echo "   JWT Issues: Usually user table/secret key issues\n";

echo "\n╔══════════════════════════════════════════════════╗\n";
echo "║                    DIAGNOSTIC COMPLETE                    ║\n";
echo "╚══════════════════════════════════════════════════════╝\n";
