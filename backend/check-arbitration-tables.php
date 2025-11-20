<?php
require_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $pdo = new PDO(
        'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'], 
        $_ENV['DB_USERNAME'], 
        $_ENV['DB_PASSWORD']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connection successful!\n";
    
    // Check if arbitration tables exist
    $stmt = $pdo->query("SHOW TABLES LIKE 'arbitration%'");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Arbitration tables found: " . implode(', ', $tables) . "\n";
    
    if (empty($tables)) {
        echo "❌ Arbitration tables do NOT exist!\n";
        
        // Try to create them
        echo "Attempting to create arbitration tables...\n";
        $sql = file_get_contents(__DIR__ . '/arbitration-tables.sql');
        $pdo->exec($sql);
        echo "✅ Arbitration tables created successfully!\n";
    } else {
        echo "✅ Arbitration tables exist!\n";
    }
    
    // Check table structures
    foreach (['arbitration_applications', 'application_documents', 'application_timeline'] as $table) {
        if (in_array($table, $tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
            $count = $stmt->fetchColumn();
            echo "Table `$table`: $count records\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}