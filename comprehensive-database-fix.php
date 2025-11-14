<?php
/**
 * BGAofis Law Office Automation - Comprehensive Database Schema Fix
 * This script fixes all database schema mismatches between application and database
 */

echo "BGAofis Law Office Automation - Comprehensive Database Schema Fix\n";
echo "================================================================\n\n";

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

// Function to check if column exists
function columnExists($connection, $table, $column) {
    try {
        $result = $connection->select("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return !empty($result);
    } catch (Exception $e) {
        return false;
    }
}

// Function to check if table exists
function tableExists($connection, $table) {
    try {
        $result = $connection->select("SHOW TABLES LIKE '$table'");
        return !empty($result);
    } catch (Exception $e) {
        return false;
    }
}

// Fix 1: Add deleted_at column to cash_transactions
echo "\n2. Fixing cash_transactions table...\n";
try {
    if (!columnExists($connection, 'cash_transactions', 'deleted_at')) {
        $connection->statement("ALTER TABLE cash_transactions ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to cash_transactions\n";
    } else {
        echo "✓ deleted_at column already exists in cash_transactions\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to fix cash_transactions: " . $e->getMessage() . "\n";
}

// Fix 2: Create workflow_templates table
echo "\n3. Creating workflow_templates table...\n";
try {
    if (!tableExists($connection, 'workflow_templates')) {
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
    } else {
        echo "✓ workflow_templates table already exists\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to create workflow_templates: " . $e->getMessage() . "\n";
}

// Fix 3: Add status column to notifications
echo "\n4. Fixing notifications table...\n";
try {
    if (!columnExists($connection, 'notifications', 'status')) {
        $connection->statement("ALTER TABLE notifications ADD COLUMN status ENUM('pending','sent','failed') DEFAULT 'pending'");
        echo "✓ Added status column to notifications\n";
    } else {
        echo "✓ status column already exists in notifications\n";
    }
    
    // Also ensure deleted_at exists in notifications
    if (!columnExists($connection, 'notifications', 'deleted_at')) {
        $connection->statement("ALTER TABLE notifications ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to notifications\n";
    } else {
        echo "✓ deleted_at column already exists in notifications\n";
    }
} catch (Exception $e) {
    echo "✗ Failed to fix notifications: " . $e->getMessage() . "\n";
}

// Fix 4: Check and fix other potential issues
echo "\n5. Checking other potential issues...\n";

// Check if workflows table exists (alternative to workflow_templates)
if (tableExists($connection, 'workflows')) {
    echo "✓ Found workflows table (alternative to workflow_templates)\n";
}

// Check table structures
$tablesToCheck = ['cash_transactions', 'notifications', 'workflows', 'workflow_templates'];
foreach ($tablesToCheck as $table) {
    if (tableExists($connection, $table)) {
        echo "  ✓ $table table exists\n";
        
        // Show structure
        try {
            $structure = $connection->select("DESCRIBE $table");
            echo "    Columns: ";
            $columns = [];
            foreach ($structure as $column) {
                $columns[] = $column->Field;
            }
            echo implode(', ', $columns) . "\n";
        } catch (Exception $e) {
            echo "    Could not get structure: " . $e->getMessage() . "\n";
        }
    } else {
        echo "  ✗ $table table missing\n";
    }
}

// Test the problematic queries
echo "\n6. Testing fixed queries...\n";
try {
    // Test dashboard queries
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'income' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard income query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'expense' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard expense query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
    // Test workflow templates query
    if (tableExists($connection, 'workflow_templates')) {
        $result = $connection->select("SELECT * FROM `workflow_templates` WHERE `workflow_templates`.`deleted_at` IS NULL ORDER BY `case_type` ASC");
        echo "✓ Workflow templates query successful: " . count($result) . " records\n";
    }
    
    // Test notifications query
    $result = $connection->select("SELECT * FROM `notifications` WHERE (`status` = 'pending') AND `notifications`.`deleted_at` IS NULL ORDER BY `created_at` DESC");
    echo "✓ Notifications query successful: " . count($result) . " records\n";
    
} catch (Exception $e) {
    echo "✗ Query test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Database Schema Fix Complete!\n\n";

echo "Summary of changes:\n";
echo "1. ✓ Added deleted_at column to cash_transactions (if missing)\n";
echo "2. ✓ Created workflow_templates table (if missing)\n";
echo "3. ✓ Added status column to notifications (if missing)\n";
echo "4. ✓ Added deleted_at column to notifications (if missing)\n\n";

echo "Next Steps:\n";
echo "1. Test your API endpoints:\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/dashboard\" -H \"Accept: application/json\"\n";
echo "2. Check your frontend application\n";
echo "3. If 405 errors persist, check API routes in backend/routes/api.php\n\n";

echo "If issues persist, please provide:\n";
echo "- Any error messages from this script\n";
echo "- Results of API endpoint tests\n";
echo "- Browser console errors\n";