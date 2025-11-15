<?php
/**
 * BGAofis Law Office Automation - Simple Diagnostic
 * This script diagnoses common API issues without complex syntax
 */

echo "BGAofis Law Office Automation - Simple Diagnostic\n";
echo "===============================================\n\n";

// Load environment variables
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "Loading environment variables from .env...\n";
    $envContent = file_get_contents($envFile);
    $lines = explode("\n", $envContent);
    
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && !empty(trim($line)) && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
            $_SERVER[trim($key)] = trim($value);
        }
    }
    echo "Environment variables loaded\n";
} else {
    echo "WARNING: .env file not found\n";
}

echo "\n1. Testing database connection...\n";
try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_DATABASE'] ?? 'haslim_bgofis';
    $username = $_ENV['DB_USERNAME'] ?? 'haslim_bgofis';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection: SUCCESS\n";
    
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "\n";
    $pdo = null;
}

echo "\n2. Checking audit_logs table...\n";
if ($pdo) {
    try {
        $columns = $pdo->query("SHOW COLUMNS FROM audit_logs")->fetchAll();
        $issues = [];
        
        foreach ($columns as $column) {
            $fieldName = $column['Field'];
            $fieldType = $column['Type'];
            
            echo "Column: $fieldName - Type: $fieldType\n";
            
            if ($fieldName === 'entity_id' && strpos($fieldType, 'varchar(36)') === false) {
                $issues[] = "entity_id column must be VARCHAR(36) for UUIDs";
            }
            
            if ($fieldName === 'user_id' && strpos($fieldType, 'varchar(36)') === false) {
                $issues[] = "user_id column must be VARCHAR(36) for UUIDs";
            }
            
            if ($fieldName === 'id' && strpos($fieldType, 'varchar(36)') === false) {
                $issues[] = "id column must be VARCHAR(36) for UUIDs";
            }
        }
        
        if (empty($issues)) {
            echo "audit_logs table: CORRECT\n";
        } else {
            echo "audit_logs table: ISSUES FOUND\n";
            foreach ($issues as $issue) {
                echo "  - $issue\n";
            }
        }
        
    } catch (Exception $e) {
        echo "Error checking audit_logs: " . $e->getMessage() . "\n";
    }
}

echo "\n3. Testing authentication system...\n";
if ($pdo) {
    try {
        // Check users table
        $usersTable = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
        if (!empty($usersTable)) {
            echo "Users table: EXISTS\n";
            
            // Check users table structure
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
            
            echo "Users table structure:\n";
            echo "  - UUID ID column: " . ($hasUuid ? "YES" : "NO") . "\n";
            echo "  - Password column: " . ($hasPassword ? "YES" : "NO") . "\n";
        } else {
            echo "Users table: MISSING\n";
        }
        
    } catch (Exception $e) {
        echo "Error checking authentication: " . $e->getMessage() . "\n";
    }
}

echo "\n4. Testing file system...\n";
$requiredFiles = [
    'routes/api.php' => 'Routes file',
    '.env' => 'Environment file',
    'app/Controllers/AuthController.php' => 'Auth Controller',
    'app/Controllers/ClientController.php' => 'Client Controller',
    'app/Middleware/AuthMiddleware.php' => 'Auth Middleware',
    'app/Middleware/AuditLogMiddleware.php' => 'Audit Log Middleware'
];

foreach ($requiredFiles as $file => $description) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "$description: EXISTS\n";
    } else {
        echo "$description: MISSING\n";
    }
}

echo "\n5. Testing API endpoints...\n";
if ($pdo) {
    $testEndpoints = [
        '/api/auth/login' => 'POST',
        '/api/clients' => 'GET',
        '/api/cases' => 'GET',
        '/api/finance/cash-stats' => 'GET',
        '/api/calendar/events' => 'GET',
        '/api/roles' => 'GET'
    ];
    
    foreach ($testEndpoints as $endpoint => $method) {
        echo "Testing $method $endpoint...\n";
        
        // Check if route exists by looking for common patterns
        $routesFile = __DIR__ . '/routes/api.php';
        if (file_exists($routesFile)) {
            $routesContent = file_get_contents($routesFile);
            $hasRoute = strpos($routesContent, $method) !== false && strpos($routesContent, $endpoint) !== false;
            
            if ($hasRoute) {
                echo "  Route pattern: FOUND\n";
            } else {
                echo "  Route pattern: MISSING\n";
            }
        } else {
            echo "  Routes file: NOT FOUND\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "DIAGNOSTIC COMPLETE\n";

echo "\nRECOMMENDATIONS:\n";
echo "1. If audit_logs table has issues, run: php fix-audit-primary-key-safe.php\n";
echo "2. If routes are missing, run: php fix-missing-routes.php\n";
echo "3. If controllers are missing, upload required controller files\n";
echo "4. If middleware is missing, upload required middleware files\n";
echo "5. Check .env file configuration\n";
echo "6. Monitor server error logs for specific error messages\n";

echo "\nCOMMON ISSUES AND SOLUTIONS:\n";
echo "- 500 Errors: Usually database schema or PHP errors\n";
echo "- 403 Forbidden: Usually authentication/authorization issues\n";
echo "- 405 Method Not Allowed: Usually missing routes or HTTP method issues\n";
echo "- 401 Unauthorized: Invalid or expired JWT tokens\n";

echo "\nRun this diagnostic to identify specific issues: php simple-diagnostic.php\n";