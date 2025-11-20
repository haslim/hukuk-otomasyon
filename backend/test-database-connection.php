<?php

$app = require __DIR__ . '/bootstrap/app.php';

use Illuminate\Database\Capsule\Manager as Capsule;

echo "Testing database connection through Eloquent...\n";

try {
    // Test basic connection
    $tables = Capsule::select('SHOW TABLES LIKE "arbitration%"');
    echo "Arbitration tables found: " . json_encode($tables) . "\n";
    
    if (empty($tables)) {
        echo "❌ Arbitration tables do NOT exist!\n";
        
        // Try to create them
        echo "Attempting to create arbitration tables...\n";
        $sql = file_get_contents(__DIR__ . '/arbitration-tables.sql');
        Capsule::statement($sql);
        echo "✅ Arbitration tables created successfully!\n";
        
        // Verify creation
        $tables = Capsule::select('SHOW TABLES LIKE "arbitration%"');
        echo "Tables after creation: " . json_encode($tables) . "\n";
    } else {
        echo "✅ Arbitration tables exist!\n";
        
        // Check table structures and counts
        foreach (['arbitration_applications', 'application_documents', 'application_timeline'] as $table) {
            try {
                $count = Capsule::table($table)->count();
                echo "Table `$table`: $count records\n";
            } catch (Exception $e) {
                echo "❌ Error accessing table `$table`: " . $e->getMessage() . "\n";
            }
        }
    }
    
    // Test if we can create a sample application
    echo "\nTesting ArbitrationApplication model...\n";
    try {
        // This will test if the model can be instantiated and if the table is accessible
        $model = new \App\Models\ArbitrationApplication();
        echo "✅ ArbitrationApplication model can be instantiated\n";
        
        // Test if we can query the table
        $count = \App\Models\ArbitrationApplication::count();
        echo "✅ Can query arbitration_applications table: $count records\n";
        
    } catch (Exception $e) {
        echo "❌ Error with ArbitrationApplication model: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}