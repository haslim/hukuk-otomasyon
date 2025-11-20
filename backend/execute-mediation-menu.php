<?php
require_once 'bootstrap/app.php';

try {
    $pdo = Illuminate\Database\Capsule\Manager::connection()->getPdo();
    $sql = file_get_contents('mediation-fee-menu-update.sql');
    
    echo "Executing mediation fee menu update SQL...\n";
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $index => $statement) {
        if (!empty($statement)) {
            echo "Statement " . ($index + 1) . ": " . substr($statement, 0, 80) . "...\n";
            $result = $pdo->exec($statement);
            echo "✓ Executed successfully\n";
        }
    }
    
    echo "\n✅ Mediation fee menu items successfully added to database!\n";
    
    // Verify the menu items were added
    echo "\n📋 Verifying added menu items:\n";
    $check = $pdo->query("SELECT path, label, icon, sort_order FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE path = '/mediation' LIMIT 1) ORDER BY sort_order");
    
    while ($row = $check->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['label']} ({$row['path']}) - Icon: {$row['icon']} - Order: {$row['sort_order']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
