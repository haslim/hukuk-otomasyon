<?php
// Simple script to execute mediation menu SQL
try {
    // Database configuration from .env
    $host = 'localhost';
    $database = 'haslim_bgofis';
    $username = 'haslim_bgofis';
    $password = 'Fener1907****';
    
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully!\n";
    
    // Read and execute SQL
    $sql = file_get_contents('mediation-fee-menu-update.sql');
    echo "Executing mediation fee menu update SQL...\n";
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $index => $statement) {
        if (!empty($statement)) {
            echo "Statement " . ($index + 1) . ": " . substr($statement, 0, 60) . "...\n";
            $result = $pdo->exec($statement);
            echo "✓ Executed successfully\n";
        }
    }
    
    echo "\n✅ Mediation fee menu items successfully added to database!\n";
    
    // Verify the menu items were added
    echo "\n📋 Verifying added menu items under mediation:\n";
    $check = $pdo->query("SELECT path, label, icon, sort_order FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE path = '/mediation' LIMIT 1) ORDER BY sort_order");
    
    $items = $check->fetchAll(PDO::FETCH_ASSOC);
    if (empty($items)) {
        echo "No menu items found under mediation menu. Let's check if mediation menu exists:\n";
        $mediationCheck = $pdo->query("SELECT id, path, label FROM menu_items WHERE path = '/mediation'");
        $mediation = $mediationCheck->fetch(PDO::FETCH_ASSOC);
        if ($mediation) {
            echo "Found mediation menu: {$mediation['label']} ({$mediation['path']}) ID: {$mediation['id']}\n";
        } else {
            echo "❌ Mediation menu not found in database!\n";
        }
    } else {
        foreach ($items as $row) {
            echo "- {$row['label']} ({$row['path']}) - Icon: {$row['icon']} - Order: {$row['sort_order']}\n";
        }
    }
    
    // Also check invoice menu items
    echo "\n📋 Verifying finance menu items (invoices):\n";
    $financeCheck = $pdo->query("SELECT path, label, icon, sort_order FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE path = '/finance/cash' LIMIT 1) ORDER BY sort_order");
    
    $financeItems = $financeCheck->fetchAll(PDO::FETCH_ASSOC);
    foreach ($financeItems as $row) {
        echo "- {$row['label']} ({$row['path']}) - Icon: {$row['icon']} - Order: {$row['sort_order']}\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "Please check database connection details.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
