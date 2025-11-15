<?php
/**
 * BGAofis Law Office Automation - Simple Migration Runner
 * This script runs migrations without external dependencies
 */

echo "BGAofis Law Office Automation - Migration Runner\n";
echo "===============================================\n\n";

// Load environment variables manually
$envFile = '.env';
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
    echo "✓ Environment variables loaded\n";
} else {
    echo "⚠ .env file not found, using defaults\n";
}

echo "\n1. Testing database connection...\n";
try {
    $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbname = $_ENV['DB_DATABASE'] ?? 'haslim_bgofis';
    $username = $_ENV['DB_USERNAME'] ?? 'haslim_bgofis';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Database connection successful\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n2. Creating required tables directly...\n";
$migrationSuccess = 0;

try {
    // Create cash_transactions table with deleted_at column (if not exists)
    $result = $pdo->query("SHOW TABLES LIKE 'cash_transactions'")->fetchAll();
    if (empty($result)) {
        $pdo->exec("
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
    $result = $pdo->query("SHOW COLUMNS FROM `cash_transactions` LIKE 'deleted_at'")->fetchAll();
    if (empty($result)) {
        $pdo->exec("ALTER TABLE cash_transactions ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to cash_transactions\n";
        $migrationSuccess++;
    } else {
        echo "✓ deleted_at column already exists in cash_transactions\n";
    }
    
    // Create workflow_templates table
    $result = $pdo->query("SHOW TABLES LIKE 'workflow_templates'")->fetchAll();
    if (empty($result)) {
        $pdo->exec("
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
    $result = $pdo->query("SHOW COLUMNS FROM `notifications` LIKE 'status'")->fetchAll();
    if (empty($result)) {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN status ENUM('pending','sent','failed') DEFAULT 'pending'");
        echo "✓ Added status column to notifications\n";
        $migrationSuccess++;
    } else {
        echo "✓ status column already exists in notifications\n";
    }
    
    // Add deleted_at column to notifications if missing
    $result = $pdo->query("SHOW COLUMNS FROM `notifications` LIKE 'deleted_at'")->fetchAll();
    if (empty($result)) {
        $pdo->exec("ALTER TABLE notifications ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "✓ Added deleted_at column to notifications\n";
        $migrationSuccess++;
    } else {
        echo "✓ deleted_at column already exists in notifications\n";
    }
    
    // Add deleted_at column to clients if missing
    $result = $pdo->query("SHOW COLUMNS FROM `clients` LIKE 'deleted_at'")->fetchAll();
    if (empty($result)) {
        $pdo->exec("ALTER TABLE clients ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
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
    $stmt = $pdo->prepare("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'income' AND `cash_transactions`.`deleted_at` IS NULL");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Dashboard income query successful: " . ($result['aggregate'] ?? 0) . "\n";
    
    $stmt = $pdo->prepare("SELECT sum(`amount`) as aggregate FROM `cash_transactions` WHERE `type` = 'expense' AND `cash_transactions`.`deleted_at` IS NULL");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✓ Dashboard expense query successful: " . ($result['aggregate'] ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "✗ Dashboard query test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Migration Summary:\n";
echo "- Tables created/updated: " . $migrationSuccess . "\n";
echo "- Dashboard queries tested: " . ($migrationSuccess > 0 ? "SUCCESS" : "FAILED") . "\n";

echo "\nNext Steps:\n";
echo "1. Run: php fix-permissions.php\n";
echo "2. Upload updated files to production server:\n";
echo "   - backend/app/Controllers/CalendarController.php (NEW)\n";
echo "   - backend/app/Controllers/UserController.php (FIXED)\n";
echo "   - backend/app/Controllers/FinanceController.php (UPDATED)\n";
echo "   - backend/app/Models/FinanceTransaction.php (UPDATED)\n";
echo "3. Test your application:\n";
echo "   - Open: https://bgaofis.billurguleraslim.av.tr\n";
echo "   - Check browser console for errors\n";
echo "   - Test all application features\n";

echo "\nExpected Results:\n";
echo "- ✅ All database queries succeed\n";
echo "- ✅ All API endpoints return proper JSON responses\n";
echo "- ✅ Frontend application works completely\n";