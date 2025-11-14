<?php
/**
 * BGAofis Law Office Automation - Table Naming Fix Script
 * This script fixes the table naming mismatch between cash_transactions and finance_transactions
 */

echo "BGAofis Law Office Automation - Table Naming Fix\n";
echo "===============================================\n\n";

// Load environment variables
if (file_exists('.env')) {
    echo "Loading environment variables from .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
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
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check current table structure
echo "\n2. Analyzing the table naming issue...\n";
try {
    // Check if cash_transactions exists
    $cashTableExists = $connection->select("SHOW TABLES LIKE 'cash_transactions'");
    if (!empty($cashTableExists)) {
        echo "✓ Found 'cash_transactions' table\n";
        
        // Get structure of cash_transactions
        $cashStructure = $connection->select("DESCRIBE cash_transactions");
        echo "  Structure of cash_transactions:\n";
        foreach ($cashStructure as $column) {
            echo "    - " . $column->Field . " (" . $column->Type . ")\n";
        }
    }
    
    // Check if finance_transactions exists
    $financeTableExists = $connection->select("SHOW TABLES LIKE 'finance_transactions'");
    if (empty($financeTableExists)) {
        echo "✗ 'finance_transactions' table does not exist (this is the problem)\n";
    } else {
        echo "✓ 'finance_transactions' table already exists\n";
    }
    
} catch (Exception $e) {
    echo "✗ Failed to analyze tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Solution options
echo "\n3. Available solutions:\n";
echo "Option A: Rename cash_transactions to finance_transactions\n";
echo "Option B: Create finance_transactions table (if different structure needed)\n";
echo "Option C: Update application code to use cash_transactions\n\n";

// Let's implement Option A (rename) since it's the most likely correct solution
echo "Implementing Option A: Renaming cash_transactions to finance_transactions...\n";

try {
    // First, let's check if finance_transactions already exists to avoid conflicts
    $financeTableExists = $connection->select("SHOW TABLES LIKE 'finance_transactions'");
    
    if (empty($financeTableExists)) {
        // Rename the table
        $connection->statement("RENAME TABLE cash_transactions TO finance_transactions");
        echo "✓ Successfully renamed 'cash_transactions' to 'finance_transactions'\n";
    } else {
        echo "! 'finance_transactions' already exists. Skipping rename.\n";
        echo "  You may need to manually merge data or choose a different approach.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Failed to rename table: " . $e->getMessage() . "\n";
    echo "  You may need to run this manually or choose a different approach.\n";
}

// Verify the fix
echo "\n4. Verifying the fix...\n";
try {
    $tables = $connection->select("SHOW TABLES");
    $foundFinanceTransactions = false;
    $foundCashTransactions = false;
    
    foreach ($tables as $table) {
        $tableName = $table->{'Tables_in_' . $_ENV['DB_DATABASE']};
        if ($tableName === 'finance_transactions') {
            $foundFinanceTransactions = true;
        }
        if ($tableName === 'cash_transactions') {
            $foundCashTransactions = true;
        }
    }
    
    if ($foundFinanceTransactions) {
        echo "✓ 'finance_transactions' table now exists\n";
    } else {
        echo "✗ 'finance_transactions' table still not found\n";
    }
    
    if (!$foundCashTransactions) {
        echo "✓ 'cash_transactions' table was successfully renamed\n";
    } else {
        echo "! 'cash_transactions' table still exists (both tables present)\n";
    }
    
} catch (Exception $e) {
    echo "✗ Failed to verify tables: " . $e->getMessage() . "\n";
}

// Test the problematic query
echo "\n5. Testing the dashboard query...\n";
try {
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `finance_transactions` WHERE `type` = 'income' AND `finance_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard income query executed successfully\n";
    echo "  Result: " . ($result[0]->aggregate ?? 0) . "\n";
    
    $result = $connection->select("SELECT sum(`amount`) as aggregate FROM `finance_transactions` WHERE `type` = 'expense' AND `finance_transactions`.`deleted_at` IS NULL");
    echo "✓ Dashboard expense query executed successfully\n";
    echo "  Result: " . ($result[0]->aggregate ?? 0) . "\n";
    
} catch (Exception $e) {
    echo "✗ Dashboard query failed: " . $e->getMessage() . "\n";
    echo "  This might indicate a column structure mismatch.\n";
    
    // Let's check the structure
    echo "\n6. Checking finance_transactions structure...\n";
    try {
        $structure = $connection->select("DESCRIBE finance_transactions");
        echo "  Current structure:\n";
        foreach ($structure as $column) {
            echo "    - " . $column->Field . " (" . $column->Type . ")\n";
        }
        
        // Check if required columns exist
        $requiredColumns = ['id', 'type', 'amount', 'deleted_at'];
        $foundColumns = [];
        foreach ($structure as $column) {
            $foundColumns[] = $column->Field;
        }
        
        $missingColumns = array_diff($requiredColumns, $foundColumns);
        if (!empty($missingColumns)) {
            echo "  Missing required columns: " . implode(', ', $missingColumns) . "\n";
            echo "  You may need to add these columns manually.\n";
        }
        
    } catch (Exception $e2) {
        echo "  Failed to check structure: " . $e2->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Table Naming Fix Complete!\n\n";

echo "Next Steps:\n";
echo "1. Test your API endpoint: GET /api/dashboard\n";
echo "2. Check your frontend application\n";
echo "3. Verify the dashboard loads without errors\n";
echo "4. If issues persist, check the column structure above\n\n";

echo "If you still have issues, you may need to:\n";
echo "- Add missing columns to finance_transactions table\n";
echo "- Or update the application model to match the actual table structure\n";
echo "- Or choose Option C (update application code to use cash_transactions)\n";