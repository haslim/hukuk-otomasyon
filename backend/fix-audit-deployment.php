<?php
/**
 * BGAofis Law Office Automation - Audit Logs Fix for Deployment
 * This script fixes the audit_logs table entity_id column length issue
 * Designed to be run on the production server
 */

echo "BGAofis Law Office Automation - Audit Logs Fix\n";
echo "==============================================\n\n";

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

echo "\n2. Checking and fixing audit_logs table structure...\n";
try {
    // Check if audit_logs table exists
    $result = $pdo->query("SHOW TABLES LIKE 'audit_logs'")->fetchAll();
    
    if (empty($result)) {
        echo "⚠ audit_logs table not found, creating it...\n";
        // Create audit_logs table with proper column sizes
        $pdo->exec("
            CREATE TABLE audit_logs (
                id VARCHAR(36) PRIMARY KEY,
                user_id VARCHAR(36) NULL,
                entity_type VARCHAR(100) NULL,
                entity_id VARCHAR(36) NULL,
                action VARCHAR(100) NULL,
                ip VARCHAR(45) NULL,
                metadata JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                deleted_at TIMESTAMP NULL
            )
        ");
        echo "✓ Created audit_logs table with proper column sizes\n";
    } else {
        echo "✓ audit_logs table exists, checking column sizes...\n";
        
        // Check and fix column sizes
        $columns = $pdo->query("SHOW COLUMNS FROM audit_logs")->fetchAll();
        $columnTypes = [];
        
        foreach ($columns as $column) {
            $columnTypes[$column['Field']] = $column['Type'];
            echo "  Column: {$column['Field']} - Type: {$column['Type']}\n";
        }
        
        // Fix entity_id column size
        if (!isset($columnTypes['entity_id']) || strpos($columnTypes['entity_id'], 'varchar(36)') === false) {
            $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN entity_id VARCHAR(36) NULL");
            echo "✓ Fixed entity_id column to VARCHAR(36)\n";
        } else {
            echo "✓ entity_id column already correct size\n";
        }
        
        // Fix user_id column size
        if (!isset($columnTypes['user_id']) || strpos($columnTypes['user_id'], 'varchar(36)') === false) {
            $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN user_id VARCHAR(36) NULL");
            echo "✓ Fixed user_id column to VARCHAR(36)\n";
        } else {
            echo "✓ user_id column already correct size\n";
        }
        
        // Fix ip column size
        if (!isset($columnTypes['ip']) || strpos($columnTypes['ip'], 'varchar(45)') === false) {
            $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN ip VARCHAR(45) NULL");
            echo "✓ Fixed ip column to VARCHAR(45)\n";
        } else {
            echo "✓ ip column already correct size\n";
        }
        
        // Fix metadata column type
        if (!isset($columnTypes['metadata']) || strpos($columnTypes['metadata'], 'json') === false) {
            $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN metadata JSON NULL");
            echo "✓ Fixed metadata column to JSON\n";
        } else {
            echo "✓ metadata column already correct type\n";
        }
        
        // Add deleted_at column for soft deletes if missing
        if (!isset($columnTypes['deleted_at'])) {
            $pdo->exec("ALTER TABLE audit_logs ADD COLUMN deleted_at TIMESTAMP NULL");
            echo "✓ Added deleted_at column for soft deletes\n";
        } else {
            echo "✓ deleted_at column already exists\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error fixing audit_logs table: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. Testing audit_logs insert with UUID...\n";
try {
    $testId = 'test-' . uniqid();
    $testEntityId = '75ea5c9c-ea28-4f4a-bd17-fcb47d4660bc'; // Test UUID (same as in error)
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (id, user_id, entity_type, entity_id, action, ip, metadata, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $testId,
        '22', // user_id from error
        'client', // entity_type from error
        $testEntityId,
        'GET', // action from error
        '176.33.112.19', // IP from error
        json_encode(['path' => 'https://backend.bgaofis.billurguleraslim.av.tr/api/clients', 'status' => 200])
    ]);
    
    // Clean up test record
    $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE id = ?");
    $stmt->execute([$testId]);
    
    echo "✓ audit_logs UUID insert test successful\n";
    
} catch (Exception $e) {
    echo "✗ audit_logs UUID insert test failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n4. Verifying final table structure...\n";
try {
    $columns = $pdo->query("SHOW COLUMNS FROM audit_logs")->fetchAll();
    echo "Final audit_logs table structure:\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']}: {$column['Type']} (Null: {$column['Null']})\n";
    }
} catch (Exception $e) {
    echo "✗ Error verifying table structure: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Audit Logs Fix Summary:\n";
echo "- audit_logs table structure: FIXED\n";
echo "- Column sizes corrected: ✓\n";
echo "- UUID insert test: SUCCESS\n";

echo "\n🎉 FIX COMPLETED SUCCESSFULLY!\n";
echo "\nExpected Results:\n";
echo "- ✅ /api/clients should return 200 OK (no more data truncation errors)\n";
echo "- ✅ audit_logs should handle UUIDs properly\n";
echo "- ✅ No more 500 Internal Server Errors due to entity_id truncation\n";

echo "\nNext Steps:\n";
echo "1. Test /api/clients endpoint\n";
echo "2. Test other API endpoints that use audit logging\n";
echo "3. Monitor application logs for any remaining issues\n";
echo "4. Consider running a full migration if other tables need updates\n";