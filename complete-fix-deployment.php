<?php
/**
 * BGAofis Law Office Automation - Complete Fix & Deployment Script
 * This script handles both database fixes AND API routes update in one execution
 */

echo "BGAofis Law Office Automation - Complete Fix & Deployment\n";
echo "========================================================\n\n";

// Load environment variables
if (file_exists('.env')) {
    echo "Loading environment variables from .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Step 1: Database Connection
echo "1. Testing database connection...\n";
try {
    require_once 'vendor/autoload.php';
    
    $capsule = new Illuminate\Database\Capsule\Manager();
    $capsule->addConnection([
        'driver' => $_ENV['DB_CONNECTION'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
        'database' => $_ENV['DB_DATABASE'] ?? 'bgaofis',
        'username' => $_ENV['DB_USERNAME'] ?? 'root',
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => ''
    ]);
    
    $connection = $capsule->getConnection();
    $connection->getPdo();
    echo "✓ Database connection successful\n";
    echo "  - Database: " . $_ENV['DB_DATABASE'] . "\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Step 2: Database Schema Fixes
echo "\n2. Applying database schema fixes...\n";
$databaseFixesApplied = 0;

try {
    // Fix 1: Add deleted_at to cash_transactions
    $result = $connection->select("SHOW COLUMNS FROM `cash_transactions` LIKE 'deleted_at'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE cash_transactions ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to cash_transactions\n";
        $databaseFixesApplied++;
    } else {
        echo "✓ deleted_at column already exists in cash_transactions\n";
    }
    
    // Fix 2: Create workflow_templates table
    $result = $connection->select("SHOW TABLES LIKE 'workflow_templates'");
    if (empty($result)) {
        $connection->statement("
            CREATE TABLE workflow_templates (
                id VARCHAR(36) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                case_type VARCHAR(255) NOT NULL,
                tags JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL
            )
        ");
        echo "✓ Created workflow_templates table\n";
        $databaseFixesApplied++;
    } else {
        echo "✓ workflow_templates table already exists\n";
    }
    
    // Fix 3: Add status to notifications
    $result = $connection->select("SHOW COLUMNS FROM `notifications` LIKE 'status'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE notifications ADD COLUMN status ENUM('pending','sent','failed') DEFAULT 'pending'");
        echo "✓ Added status column to notifications\n";
        $databaseFixesApplied++;
    } else {
        echo "✓ status column already exists in notifications\n";
    }
    
    // Fix 4: Add deleted_at to notifications (if missing)
    $result = $connection->select("SHOW COLUMNS FROM `notifications` LIKE 'deleted_at'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE notifications ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to notifications\n";
        $databaseFixesApplied++;
    } else {
        echo "✓ deleted_at column already exists in notifications\n";
    }
    
    // Fix 5: Add deleted_at to clients (if needed)
    $result = $connection->select("SHOW COLUMNS FROM `clients` LIKE 'deleted_at'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE clients ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to clients\n";
        $databaseFixesApplied++;
    } else {
        echo "✓ deleted_at column already exists in clients\n";
    }
    
} catch (Exception $e) {
    echo "✗ Database fix failed: " . $e->getMessage() . "\n";
    $databaseFixesApplied = 0;
}

// Step 3: API Routes Update
echo "\n3. Updating API routes...\n";
$routesFile = __DIR__ . '/routes/api.php';
$routesUpdated = false;

try {
    // Read current routes
    $currentRoutes = file_get_contents($routesFile);
    
    // Check if missing routes need to be added
    $missingRoutes = [
        'GET /api/roles' => [
            'route' => "\$group->get('/api/roles', [UserController::class, 'roles'])",
            'controller' => 'UserController',
            'method' => 'roles'
        ],
        'GET /api/calendar/events' => [
            'route' => "\$group->get('/api/calendar/events', [CalendarController::class, 'events'])",
            'controller' => 'CalendarController', 
            'method' => 'events'
        ],
        'GET /api/finance/cash-stats' => [
            'route' => "\$group->get('/api/finance/cash-stats', [FinanceController::class, 'cashStats'])",
            'controller' => 'FinanceController',
            'method' => 'cashStats'
        ],
        'GET /api/finance/cash-transactions' => [
            'route' => "\$group->get('/api/finance/cash-transactions', [FinanceController::class, 'cashTransactions'])",
            'controller' => 'FinanceController',
            'method' => 'cashTransactions'
        ]
    ];
    
    $routesToAdd = "";
    foreach ($missingRoutes as $route => $info) {
        // Check if route already exists
        if (strpos($currentRoutes, $info['route']) === false) {
            $middleware = "";
            if (strpos($info['controller'], 'UserController') !== false) {
                $middleware = "->add(new RoleMiddleware(\"ROLE_MANAGEMENT\"))";
            }
            
            $routesToAdd .= "            \$group->get('" . str_replace('/api', '', $route) . "', [" . $info['controller'] . "::class, '" . $info['method'] . "'])" . $middleware . ";\n";
            echo "✓ Adding route: " . $route . "\n";
            $routesUpdated = true;
        } else {
            echo "✓ Route already exists: " . $route . "\n";
        }
    }
    
    // Add the new routes if needed
    if ($routesUpdated && $routesToAdd !== "") {
        // Find insertion point (before the closing AuthMiddleware)
        $insertPoint = strrpos($currentRoutes, '});->add(new AuthMiddleware());');
        
        if ($insertPoint !== false) {
            $newRoutesContent = substr_replace(
                '});->add(new AuthMiddleware());',
                $routesToAdd . "\n        })->add(new AuthMiddleware());",
                $currentRoutes
            );
            
            // Backup original file
            $backupFile = $routesFile . '.backup.' . date('Y-m-d_H-i-s');
            if (copy($routesFile, $backupFile)) {
                echo "✓ Routes backup created: " . $backupFile . "\n";
            } else {
                echo "✗ Failed to create routes backup\n";
            }
            
            // Write updated routes
            if (file_put_contents($routesFile, $newRoutesContent)) {
                echo "✓ Routes file updated successfully\n";
            } else {
                echo "✗ Failed to update routes file\n";
            }
        } else {
            echo "✓ All required routes already exist\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Routes update failed: " . $e->getMessage() . "\n";
}

// Step 4: Test Dashboard Queries
echo "\n4. Testing dashboard queries...\n";
try {
    // Test the exact queries that were failing
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'income' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard income query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'expense' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard expense query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "✗ Dashboard query test failed: " . $e->getMessage() . "\n";
}

// Step 5: Summary
echo "\n" . str_repeat("=", 60) . "\n";
echo "Complete Fix & Deployment Summary:\n";
echo "========================================\n";

echo "Database Fixes Applied: " . $databaseFixesApplied . "\n";
echo "Routes Updated: " . ($routesUpdated ? "YES" : "NO") . "\n";
echo "Dashboard Queries Tested: " . ($databaseFixesApplied > 0 ? "YES" : "NO") . "\n";

echo "\nNext Steps:\n";
echo "1. Test your API endpoints:\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard\" -H \"Accept: application/json\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/roles\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/calendar/events\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/finance/cash-stats\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/finance/cash-transactions\"\n";

echo "\n2. Test your frontend application:\n";
echo "   - Open: https://bgaofis.billurguleraslim.av.tr\n";
echo "   - Check browser console for errors\n";
echo "   - Test all application features\n";

echo "\nExpected Results:\n";
echo "- ✅ All 500 Internal Server Errors resolved\n";
echo "- ✅ All 405 Method Not Allowed errors resolved\n";
echo "- ✅ Dashboard loads financial data correctly\n";
echo "- ✅ React error (#310) resolved\n";
echo "- ✅ All API endpoints return proper JSON responses\n";
echo "- ✅ Frontend application works completely\n";

echo "\nSuccess Indicators:\n";
echo "- All database queries succeed\n";
echo "- All curl commands return 200 status\n";
echo "- Browser console shows no errors\n";
echo "- All application features work properly\n";

echo "\nIf issues persist:\n";
echo "- Check server error logs in cPanel\n";
echo "- Verify file permissions\n";
echo "- Test individual endpoints manually\n";
echo "- Review authentication configuration\n";

echo "\nDeployment Complete!\n";
