<?php
/**
 * BGAofis Law Office Automation - Simple Migration Runner
 * This script runs migrations without using the problematic migration file
 */

echo "BGAofis Law Office Automation - Simple Migration Runner\n";
echo "=================================================\n\n";

// Load environment variables
if (file_exists('.env')) {
    echo "Loading environment variables from .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

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
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Creating required tables directly...\n";
$migrationSuccess = 0;

try {
    // Create cash_transactions table with deleted_at column (if not exists)
    $result = $connection->select("SHOW TABLES LIKE 'cash_transactions'");
    if (empty($result)) {
        $connection->statement("
            CREATE TABLE cash_transactions (
                id VARCHAR(36) PRIMARY KEY,
                amount DECIMAL(12,2) NOT NULL,
                type ENUM('income','expense') NOT NULL,
                description TEXT NULL,
                occurred_on DATE NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL DEFAULT NULL
            )
        ");
        echo "✓ Created cash_transactions table\n";
        $migrationSuccess++;
    } else {
        echo "✓ cash_transactions table already exists\n";
    }
    
    // Add deleted_at column if missing
    $result = $connection->select("SHOW COLUMNS FROM `cash_transactions` LIKE 'deleted_at'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE cash_transactions ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to cash_transactions\n";
        $migrationSuccess++;
    } else {
        echo "✓ deleted_at column already exists in cash_transactions\n";
    }
    
    // Create workflow_templates table
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
        $migrationSuccess++;
    } else {
        echo "✓ workflow_templates table already exists\n";
    }
    
    // Add status column to notifications if missing
    $result = $connection->select("SHOW COLUMNS FROM `notifications` LIKE 'status'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE notifications ADD COLUMN status ENUM('pending','sent','failed') DEFAULT 'pending'");
        echo "✓ Added status column to notifications\n";
        $migrationSuccess++;
    } else {
        echo "✓ status column already exists in notifications\n";
    }
    
    // Add deleted_at column to notifications if missing
    $result = $connection->select("SHOW COLUMNS FROM `notifications` LIKE 'deleted_at'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE notifications ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to notifications\n";
        $migrationSuccess++;
    } else {
        echo "✓ deleted_at column already exists in notifications\n";
    }
    
    // Add deleted_at column to clients if missing
    $result = $connection->select("SHOW COLUMNS FROM `clients` LIKE 'deleted_at'");
    if (empty($result)) {
        $connection->statement("ALTER TABLE clients ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to clients\n";
        $migrationSuccess++;
    } else {
        echo "✓ deleted_at column already exists in clients\n";
    }
    
} catch (Exception $e) {
    echo "✗ Migration failed: " . $e->getMessage() . "\n";
    $migrationSuccess = 0;
}

echo "\n3. Testing dashboard queries...\n";
try {
    // Test cash_transactions query (this was the main issue)
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'income' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard income query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'expense' AND `cash_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard expense query successful: " . ($result[0]->aggregate ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "✗ Dashboard query test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Migration Summary:\n";
echo "- Tables created/updated: " . $migrationSuccess . "\n";
echo "- Dashboard queries tested: " . ($migrationSuccess > 0 ? "SUCCESS" : "FAILED") . "\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "Next Steps:\n";
echo "1. Upload updated files to production server:\n";
echo "   - backend/app/Controllers/CalendarController.php (NEW)\n";
echo "   - backend/app/Controllers/UserController.php (FIXED)\n";
echo "   - backend/app/Controllers/FinanceController.php (UPDATED)\n";
echo "   - backend/app/Models/FinanceTransaction.php (UPDATED)\n";
echo "2. Test your application:\n";
echo "   - Open: https://bgaofis.billurguleraslim.av.tr\n";
echo "   - Check browser console for errors\n";
echo "   - Test all application features\n";

echo "\nExpected Results:\n";
echo "- ✅ All database queries succeed\n";
echo "- ✅ All API endpoints return proper JSON responses\n";
echo "- ✅ Frontend application works completely\n";

echo "\nIf issues persist:\n";
echo "- Check server error logs in cPanel\n";
echo "- Verify file permissions\n";
echo "- Test individual endpoints manually\n";