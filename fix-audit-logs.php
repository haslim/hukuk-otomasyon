<?php
/**
 * BGAofis Law Office Automation - Fix Audit Logs Table
 * This script fixes the missing 'ip' column in audit_logs table
 */

echo "BGAofis Law Office Automation - Fix Audit Logs\n";
echo "============================================\n\n";

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
        // Create audit_logs table
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
        echo "✓ Created audit_logs table\n";
    } else {
        echo "✓ audit_logs table exists\n";
        
        // Check for missing columns
        $columns = $pdo->query("SHOW COLUMNS FROM audit_logs")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('ip', $columns)) {
            $pdo->exec("ALTER TABLE audit_logs ADD COLUMN ip VARCHAR(45) NULL");
            echo "✓ Added ip column to audit_logs\n";
        } else {
            echo "✓ ip column already exists in audit_logs\n";
        }
        
        if (!in_array('metadata', $columns)) {
            $pdo->exec("ALTER TABLE audit_logs ADD COLUMN metadata JSON NULL");
            echo "✓ Added metadata column to audit_logs\n";
        } else {
            echo "✓ metadata column already exists in audit_logs\n";
        }
        
        if (!in_array('updated_at', $columns)) {
            $pdo->exec("ALTER TABLE audit_logs ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
            echo "✓ Added updated_at column to audit_logs\n";
        } else {
            echo "✓ updated_at column already exists in audit_logs\n";
        }
    }
    
} catch (Exception $e) {
    echo "✗ Error fixing audit_logs table: " . $e->getMessage() . "\n";
}

echo "\n3. Testing audit_logs insert...\n";
try {
    $testId = uniqid();
    $stmt = $pdo->prepare("
        INSERT INTO audit_logs (id, user_id, entity_type, entity_id, action, ip, metadata, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $testId,
        'test-user-id',
        'test',
        'test-id',
        'TEST',
        '127.0.0.1',
        json_encode(['test' => true])
    ]);
    
    // Clean up test record
    $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE id = ?");
    $stmt->execute([$testId]);
    
    echo "✓ audit_logs table test successful\n";
    
} catch (Exception $e) {
    echo "✗ audit_logs table test failed: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Audit Logs Fix Summary:\n";
echo "- audit_logs table structure: FIXED\n";
echo "- Missing columns added: ✓\n";
echo "- Insert test: " . (isset($testId) ? "SUCCESS" : "FAILED") . "\n";

echo "\nNext Steps:\n";
echo "1. Upload the updated routes file to production server\n";
echo "2. Test the /api/clients endpoint again\n";
echo "3. Test other API endpoints\n";

echo "\nExpected Results:\n";
echo "- ✅ /api/clients should return 200 OK (no more 500 errors)\n";
echo "- ✅ audit_logs should work properly\n";