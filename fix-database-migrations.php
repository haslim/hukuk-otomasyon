<?php
/**
 * BGAofis Law Office Automation - Database Migration Fix Script
 * This script helps diagnose and fix missing database tables on production
 */

echo "BGAofis Law Office Automation - Database Migration Fix\n";
echo "=====================================================\n\n";

// Check if we're in the backend directory
if (!file_exists('database/migrate.php')) {
    echo "ERROR: This script must be run from the backend directory!\n";
    echo "Please navigate to your backend directory first.\n";
    exit(1);
}

// Load environment variables
if (file_exists('.env')) {
    echo "Loading environment variables from .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} else {
    echo "WARNING: .env file not found. Using default configuration.\n";
}

// Test database connection
echo "\n1. Testing database connection...\n";
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
    echo "  - Host: " . $_ENV['DB_HOST'] . "\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in .env file\n";
    exit(1);
}

// Check current tables
echo "\n2. Checking current database tables...\n";
try {
    $tables = $connection->select('SHOW TABLES');
    $currentTables = [];
    foreach ($tables as $table) {
        $tableName = $table->{'Tables_in_' . $_ENV['DB_DATABASE']};
        $currentTables[] = $tableName;
        echo "  - " . $tableName . "\n";
    }
    
    if (empty($currentTables)) {
        echo "  No tables found in database.\n";
    } else {
        echo "  Found " . count($currentTables) . " tables.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Failed to list tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Expected tables based on migration files
$expectedTables = [
    'users',
    'roles', 
    'permissions',
    'cases',
    'case_parties',
    'hearings',
    'documents',
    'document_versions',
    'finance_transactions', // This is the missing one causing the 500 error
    'workflow_templates',
    'workflow_steps',
    'notifications',
    'pending_notifications',
    'audit_logs',
    'tasks'
];

// Check for missing tables
echo "\n3. Checking for missing tables...\n";
$missingTables = array_diff($expectedTables, $currentTables);

if (empty($missingTables)) {
    echo "✓ All expected tables are present!\n";
} else {
    echo "✗ Missing tables found:\n";
    foreach ($missingTables as $table) {
        echo "  - " . $table . "\n";
    }
    echo "\nThese missing tables are causing the 500 Internal Server Error.\n";
}

// Run migrations if needed
if (!empty($missingTables)) {
    echo "\n4. Running database migrations...\n";
    
    try {
        // Include and run the migrate script
        echo "Executing migration script...\n";
        
        $files = glob(__DIR__ . '/database/migrations/*.php');
        sort($files);
        
        foreach ($files as $file) {
            echo "  Processing: " . basename($file) . "\n";
            $migration = require $file;
            if (is_object($migration) && method_exists($migration, 'up')) {
                $migration->up();
                echo "    ✓ Migrated\n";
            }
        }
        
        echo "✓ All migrations completed successfully!\n";
        
    } catch (Exception $e) {
        echo "✗ Migration failed: " . $e->getMessage() . "\n";
        echo "Please check the error and fix any issues manually.\n";
        exit(1);
    }
    
    // Verify tables were created
    echo "\n5. Verifying tables were created...\n";
    try {
        $newTables = $connection->select('SHOW TABLES');
        $updatedTables = [];
        foreach ($newTables as $table) {
            $updatedTables[] = $table->{'Tables_in_' . $_ENV['DB_DATABASE']};
        }
        
        $stillMissing = array_diff($expectedTables, $updatedTables);
        
        if (empty($stillMissing)) {
            echo "✓ All expected tables are now present!\n";
            echo "  Total tables: " . count($updatedTables) . "\n";
        } else {
            echo "✗ Some tables are still missing:\n";
            foreach ($stillMissing as $table) {
                echo "  - " . $table . "\n";
            }
        }
        
    } catch (Exception $e) {
        echo "✗ Failed to verify tables: " . $e->getMessage() . "\n";
    }
}

// Test specific problematic query
echo "\n6. Testing the problematic dashboard query...\n";
try {
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `finance_transactions` WHERE `type` = 'income' AND `finance_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard income query executed successfully\n";
    echo "  Result: " . ($result[0]->aggregate ?? 0) . "\n";
    
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `finance_transactions` WHERE `type` = 'expense' AND `finance_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard expense query executed successfully\n";
    echo "  Result: " . ($result[0]->aggregate ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "✗ Dashboard query failed: " . $e->getMessage() . "\n";
}

// Test API endpoint if possible
echo "\n7. Testing API endpoint availability...\n";
$apiUrl = ($_ENV['APP_URL'] ?? '') . '/api/dashboard';
if ($apiUrl && $apiUrl !== '/api/dashboard') {
    echo "You can test the API endpoint manually:\n";
    echo "  GET " . $apiUrl . "\n";
    echo "  Or run: curl -X GET \"" . $apiUrl . "\" -H \"Accept: application/json\"\n";
} else {
    echo "Please test your API endpoint manually:\n";
    echo "  GET https://yourdomain.com/backend/api/dashboard\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Database Migration Fix Complete!\n";

if (!empty($missingTables)) {
    echo "\nNext Steps:\n";
    echo "1. Test your frontend application\n";
    echo "2. Check browser console for errors\n";
    echo "3. Verify dashboard functionality works\n";
    echo "4. Run the deployment check script: php deploy.php\n";
} else {
    echo "\nYour database appears to be properly configured.\n";
    echo "If you're still experiencing issues, please check:\n";
    echo "1. Frontend API configuration\n";
    echo "2. CORS settings\n";
    echo "3. Server error logs\n";
}

echo "\nIf you need further assistance, please provide:\n";
echo "- Any error messages from this script\n";
echo "- Browser console errors\n";
echo "- Server error logs\n";