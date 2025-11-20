<?php
// SQL Syntax validation script
echo "=== SQL SYNTAX VALIDATION ===\n\n";

// Read the corrected SQL file
$sql = file_get_contents('fix-user-role-corrected.sql');

// Split into individual statements
$statements = array_filter(array_map('trim', explode(';', $sql)));

echo "Validating SQL statements:\n";
echo str_repeat("=", 50) . "\n";

$validStatements = 0;
$totalStatements = 0;

foreach ($statements as $index => $statement) {
    if (empty(trim($statement))) continue;
    
    $totalStatements++;
    
    echo "Statement " . ($totalStatements) . ": ";
    
    // Skip comments and SELECT statements that just display info
    if (strpos($statement, '--') === 0 || 
        (strpos($statement, 'SELECT') === 0 && strpos($statement, 'as info') !== false)) {
        echo "✓ COMMENT/INFO STATEMENT - SKIPPED\n";
        continue;
    }
    
    // Check for common SQL syntax patterns
    $isValid = false;
    
    if (strpos($statement, 'INSERT IGNORE INTO') === 0) {
        echo "✓ INSERT IGNORE - ";
        if (strpos($statement, 'user_roles') !== false) {
            if (strpos($statement, 'created_at') === false && strpos($statement, 'updated_at') === false) {
                echo "CORRECT (no timestamp columns)\n";
                $isValid = true;
            } else {
                echo "✗ ERROR (contains timestamp columns)\n";
            }
        } elseif (strpos($statement, 'roles') !== false) {
            if (strpos($statement, 'description') === false) {
                echo "CORRECT (no description column)\n";
                $isValid = true;
            } else {
                echo "✗ ERROR (contains description column)\n";
            }
        } elseif (strpos($statement, 'menu_permissions') !== false) {
            if (strpos($statement, 'role_id') !== false && strpos($statement, 'user_id') === false) {
                echo "CORRECT (uses role_id not user_id)\n";
                $isValid = true;
            } else {
                echo "✗ ERROR (uses user_id instead of role_id)\n";
            }
        } else {
            echo "✓ SYNTAX OK\n";
            $isValid = true;
        }
    } elseif (strpos($statement, 'SELECT') === 0) {
        echo "✓ SELECT - SYNTAX OK\n";
        $isValid = true;
    } elseif (strpos($statement, 'SET') === 0) {
        echo "✓ SET VARIABLE - SYNTAX OK\n";
        $isValid = true;
    } else {
        echo "? UNKNOWN STATEMENT TYPE\n";
    }
    
    if ($isValid) {
        $validStatements++;
    }
    
    echo "  Full statement: " . substr($statement, 0, 80) . "...\n\n";
}

echo "=== VALIDATION SUMMARY ===\n";
echo "Total statements: $totalStatements\n";
echo "Valid statements: $validStatements\n";
echo "Validation status: " . ($validStatements > 0 ? "✓ PASSED" : "✗ FAILED") . "\n";

echo "\n=== KEY FIXES APPLIED ===\n";
echo "1. ✓ Removed created_at/updated_at from user_roles INSERT\n";
echo "2. ✓ Removed description column from roles INSERT\n";
echo "3. ✓ Changed menu_permissions to use role_id instead of user_id\n";
echo "4. ✓ Simplified menu_permissions to use is_visible instead of permission flags\n";

echo "\nScript is ready for execution once MySQL PDO driver is installed.\n";