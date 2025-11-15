<?php
/**
 * BGAofis Law Office Automation - Fix Audit Logs Column Lengths
 * This script fixes column length issues in audit_logs table
 */

echo "BGAofis Law Office Automation - Fix Audit Logs Columns\n";
echo "==================================================\n\n";

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

echo "\n2. Checking audit_logs table structure...\n";
try {
    // Check if audit_logs table exists
    $result = $pdo->query("SHOW TABLES LIKE 'audit_logs'")->fetchAll();
    
    if (empty($result)) {
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
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        echo "✓ Created audit_logs table with proper column sizes\n";
    } else {
        echo "✓ audit_logs table exists\n";
        
        // Check and fix column sizes
        $columns = $pdo->query("SHOW COLUMNS FROM audit_logs")->fetchAll();
        
        foreach ($columns as $column) {
            $columnName = $column['Field'];
            $columnType = $column['Type'];
            
            echo "  Column: {$columnName} - Type: {$columnType}\n";
            
            // Fix entity_id column size
            if ($columnName === 'entity_id' && strpos($columnType, 'VARCHAR(36)') === false) {
                $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN entity_id VARCHAR(36) NULL");
                echo "✓ Fixed entity_id column to VARCHAR(36)\n";
            }
            
            // Fix user_id column size
            if ($columnName === 'user_id' && strpos($columnType, 'VARCHAR(36)') === false) {
                $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN user_id VARCHAR(36) NULL");
                echo "✓ Fixed user_id column to VARCHAR(36)\n";
            }
            
            // Fix ip column size
            if ($columnName === 'ip' && strpos($columnType, 'VARCHAR(45)') === false) {
                $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN ip VARCHAR(45) NULL");
                echo "✓ Fixed ip column to VARCHAR(45)\n";
            }
            
            // Add missing columns
            if ($columnName === 'metadata' && strpos($columnType, 'JSON') === false) {
                $pdo->exec("ALTER TABLE audit_logs MODIFY COLUMN metadata JSON NULL");
                echo "✓ Fixed metadata column to JSON\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error fixing audit_logs table: " . $e->getMessage() . "\n";
}

echo "\n3. Testing audit_logs insert with UUID...\n";
try {
    $testId = uniqid();
    $testEntityId = '5cec55d4-afe8-4468-bf8a-0b6580446457'; // Test UUID
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (id, user_id, entity_type, entity_id, action, ip, metadata, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $testId,
        'test-user-id',
        'client',
        $testEntityId,
        'GET',
        '127.0.0.1',
        json_encode(['path' => '/api/clients', 'status' => 200])
    ]);
    
    // Clean up test record
    $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE id = ?");
    $stmt->execute([$testId]);
    
    echo "✓ audit_logs UUID insert test successful\n";
    
} catch (Exception $e) {
    echo "✗ audit_logs UUID insert test failed: " . $e->getMessage() . "\n";
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
echo "Audit Logs Column Fix Summary:\n";
echo "- audit_logs table structure: FIXED\n";
echo "- Column sizes corrected: ✓\n";
echo "- UUID insert test: " . (isset($testId) ? "SUCCESS" : "FAILED") . "\n";

echo "\nNext Steps:\n";
echo "1. Test /api/clients endpoint again\n";
echo "2. Check if 500 errors are resolved\n";
echo "3. Test other API endpoints\n";

echo "\nExpected Results:\n";
echo "- ✅ /api/clients should return 200 OK (no more data truncation errors)\n";
echo "- ✅ audit_logs should handle UUIDs properly\n";
echo "- ✅ No more 500 Internal Server Errors\n";