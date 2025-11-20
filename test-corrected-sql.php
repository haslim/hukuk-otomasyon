<?php
// Test script for the corrected user role SQL
require_once 'backend/bootstrap/app.php';

try {
    echo "Testing corrected SQL script...\n\n";
    
    // Read the corrected SQL file
    $sql = file_get_contents('fix-user-role-corrected.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "Executing SQL statements:\n";
    echo str_repeat("=", 50) . "\n";
    
    foreach ($statements as $index => $statement) {
        if (empty(trim($statement))) continue;
        
        echo "Statement " . ($index + 1) . ": " . substr($statement, 0, 100) . "...\n";
        
        try {
            $result = \Illuminate\Database\Capsule\Manager::statement($statement);
            echo "✓ SUCCESS\n";
        } catch (Exception $e) {
            echo "✗ ERROR: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    echo "Test completed!\n";
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
}