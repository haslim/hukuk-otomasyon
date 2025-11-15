<?php
/**
 * BGAofis Law Office Automation - Production Diagnosis Script
 * This script helps diagnose what's still causing 500 errors
 */

echo "BGAofis Law Office Automation - Production Diagnosis\n";
echo "===============================================\n\n";

// Load environment variables
if (file_exists('.env')) {
    echo "Loading environment variables from .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Test database connection
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

// Test the specific dashboard query that's failing
echo "\n2. Testing dashboard queries...\n";
try {
    // Test cash_transactions query (this was the main issue)
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'income' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard income query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'expense' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard expense query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
    // Test workflow_templates query
    $result = $connection->select("SELECT * FROM `workflow_templates` WHERE `workflow_templates`.`deleted_at` IS NULL ORDER BY `case_type` ASC");
    echo "✓ Workflow templates query successful: " . count($result) . " records\n";
    
    // Test notifications query
    $result = $connection->select("SELECT * FROM `notifications` WHERE (`status` = 'pending') AND `notifications`.`deleted_at` IS NULL ORDER BY `created_at` DESC");
    echo "✓ Notifications query successful: " . count($result) . " records\n";
    
} catch (Exception $e) {
    echo "✗ Query test failed: " . $e->getMessage() . "\n";
    echo "  This indicates database schema issues are NOT fixed yet.\n";
}

// Check if required tables exist
echo "\n3. Checking required tables...\n";
$requiredTables = [
    'cash_transactions' => ['amount', 'type', 'deleted_at'],
    'workflow_templates' => ['id', 'name', 'case_type', 'deleted_at'],
    'notifications' => ['id', 'status', 'deleted_at']
];

foreach ($requiredTables as $table => $requiredColumns) {
    try {
        if ($connection->getSchemaBuilder()->hasTable($table)) {
            echo "✓ Table '$table' exists\n";
            
            // Check columns
            $columns = $connection->getSchemaBuilder()->getColumnListing($table);
            $missingColumns = array_diff($requiredColumns, array_keys($columns));
            
            if (empty($missingColumns)) {
                echo "  ✓ All required columns present: " . implode(', ', $requiredColumns) . "\n";
            } else {
                echo "  ✗ Missing columns: " . implode(', ', $missingColumns) . "\n";
                echo "  Available columns: " . implode(', ', array_keys($columns)) . "\n";
            }
        } else {
            echo "✗ Table '$table' does NOT exist\n";
        }
    } catch (Exception $e) {
        echo "✗ Error checking table '$table': " . $e->getMessage() . "\n";
    }
}

// Test API endpoints directly
echo "\n4. Testing API endpoints...\n";
$baseUrl = $_ENV['APP_URL'] ?? 'https://bgaofis.billurguleraslim.av.tr';
$endpoints = [
    '/api/dashboard' => 'Dashboard',
    '/api/roles' => 'Roles',
    '/api/calendar/events' => 'Calendar Events',
    '/api/finance/cash-stats' => 'Finance Cash Stats',
    '/api/finance/cash-transactions' => 'Finance Cash Transactions'
];

foreach ($endpoints as $endpoint => $name) {
    echo "Testing $name endpoint: $baseUrl$endpoint\n";
    
    // Use curl to test
    $command = "curl -X GET \"$baseUrl$endpoint\" -H \"Accept: application/json\" -w \"HTTP Status: %{http_code}\n\" -s";
    
    echo "  Command: $command\n";
    
    // Execute and capture output
    $output = [];
    $returnCode = 0;
    exec($command, $output, $returnCode);
    
    if ($returnCode === 0) {
        foreach ($output as $line) {
            if (strpos($line, 'HTTP Status:') !== false) {
                echo "  $line\n";
            }
        }
    } else {
        echo "  ✗ Failed to test endpoint\n";
    }
    
    echo "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Diagnosis Complete!\n\n";

echo "Summary:\n";
echo "1. Database connection and schema status\n";
echo "2. API endpoint accessibility\n";
echo "3. Specific query results\n\n";

echo "Next Steps:\n";
echo "1. If any tests failed above, run the database fix script:\n";
echo "   php comprehensive-database-fix.php\n\n";
echo "2. If API endpoints return 405, upload the updated routes file\n";
echo "3. If API endpoints return 500, check server error logs\n\n";

echo "Expected Results After Fixes:\n";
echo "- All database queries should succeed\n";
echo "- All API endpoints should return 200 status\n";
echo "- Frontend should load without errors\n";
echo "- React error #310 should be resolved\n";