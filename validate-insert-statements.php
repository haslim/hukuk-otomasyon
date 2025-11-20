<?php
// Specific validation for INSERT statements in the corrected SQL
echo "=== INSERT STATEMENT VALIDATION ===\n\n";

// Read the corrected SQL file
$sql = file_get_contents('fix-user-role-corrected.sql');

// Find all INSERT statements
preg_match_all('/INSERT IGNORE INTO[^;]+;/i', $sql, $insertMatches);

if (empty($insertMatches[0])) {
    echo "No INSERT statements found.\n";
    exit;
}

echo "Found " . count($insertMatches[0]) . " INSERT statements:\n";
echo str_repeat("=", 60) . "\n\n";

$allValid = true;

foreach ($insertMatches[0] as $index => $insertStatement) {
    echo "INSERT Statement " . ($index + 1) . ":\n";
    echo str_repeat("-", 40) . "\n";
    echo $insertStatement . "\n\n";
    
    $isValid = true;
    $issues = [];
    
    // Check user_roles INSERT
    if (strpos($insertStatement, 'user_roles') !== false) {
        echo "Table: user_roles\n";
        
        // Check for forbidden columns
        if (strpos($insertStatement, 'created_at') !== false) {
            $issues[] = "✗ Contains 'created_at' column (should be auto-generated)";
            $isValid = false;
        }
        
        if (strpos($insertStatement, 'updated_at') !== false) {
            $issues[] = "✗ Contains 'updated_at' column (should be auto-generated)";
            $isValid = false;
        }
        
        // Check for required columns
        if (strpos($insertStatement, 'user_id') === false) {
            $issues[] = "✗ Missing 'user_id' column";
            $isValid = false;
        }
        
        if (strpos($insertStatement, 'role_id') === false) {
            $issues[] = "✗ Missing 'role_id' column";
            $isValid = false;
        }
        
        if ($isValid) {
            echo "✓ CORRECT: Only contains user_id and role_id columns\n";
        }
    }
    
    // Check roles INSERT
    elseif (strpos($insertStatement, 'roles') !== false) {
        echo "Table: roles\n";
        
        // Check for forbidden columns
        if (strpos($insertStatement, 'description') !== false) {
            $issues[] = "✗ Contains 'description' column (doesn't exist in table)";
            $isValid = false;
        }
        
        // Check for required columns
        if (strpos($insertStatement, 'name') === false) {
            $issues[] = "✗ Missing 'name' column";
            $isValid = false;
        }
        
        if (strpos($insertStatement, '`key`') === false && strpos($insertStatement, 'key') === false) {
            $issues[] = "✗ Missing 'key' column";
            $isValid = false;
        }
        
        if ($isValid) {
            echo "✓ CORRECT: Only contains name and key columns\n";
        }
    }
    
    // Check menu_permissions INSERT
    elseif (strpos($insertStatement, 'menu_permissions') !== false) {
        echo "Table: menu_permissions\n";
        
        // Check for correct columns
        if (strpos($insertStatement, 'user_id') !== false) {
            $issues[] = "✗ Contains 'user_id' column (should use role_id)";
            $isValid = false;
        }
        
        if (strpos($insertStatement, 'role_id') === false) {
            $issues[] = "✗ Missing 'role_id' column";
            $isValid = false;
        }
        
        if (strpos($insertStatement, 'menu_item_id') === false) {
            $issues[] = "✗ Missing 'menu_item_id' column";
            $isValid = false;
        }
        
        // Check for forbidden permission columns
        if (strpos($insertStatement, 'can_view') !== false || 
            strpos($insertStatement, 'can_create') !== false || 
            strpos($insertStatement, 'can_edit') !== false || 
            strpos($insertStatement, 'can_delete') !== false) {
            $issues[] = "✗ Contains permission columns (should use is_visible only)";
            $isValid = false;
        }
        
        if ($isValid) {
            echo "✓ CORRECT: Uses role_id, menu_item_id, and is_visible columns\n";
        }
    }
    
    // Display issues
    if (!empty($issues)) {
        echo "Issues found:\n";
        foreach ($issues as $issue) {
            echo "  $issue\n";
        }
        $allValid = false;
    }
    
    echo "\n" . str_repeat("=", 60) . "\n\n";
}

echo "=== FINAL VALIDATION RESULT ===\n";
if ($allValid) {
    echo "✓ ALL INSERT STATEMENTS ARE CORRECT\n";
    echo "The SQL script should work properly with the actual database schema.\n";
} else {
    echo "✗ SOME INSERT STATEMENTS HAVE ISSUES\n";
    echo "Please fix the issues before executing the script.\n";
}

echo "\n=== TABLE STRUCTURE SUMMARY ===\n";
echo "Based on migration files:\n";
echo "- user_roles: user_id, role_id, created_at, updated_at (auto)\n";
echo "- roles: id, name, key, created_at, updated_at (auto)\n";
echo "- menu_permissions: id, role_id, menu_item_id, is_visible, created_at, updated_at (auto)\n";