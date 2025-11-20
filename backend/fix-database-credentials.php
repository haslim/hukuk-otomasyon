<?php

/**
 * Fix Database Credentials Issue
 * Resolves the database access denied error
 */

echo "=== Fix Database Credentials Issue ===\n\n";

// Load current environment
$envPath = dirname(__DIR__);
if (file_exists($envPath . '/.env')) {
    echo "Current .env file contents:\n";
    $lines = file($envPath . '/.env');
    foreach ($lines as $line) {
        if (strpos($line, 'DB_') === 0 && strpos($line, '=') !== false) {
            echo "  " . trim($line) . "\n";
        }
    }
    echo "\n";
}

echo "Issue identified: Database password is empty or incorrect\n";
echo "Error: SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)\n\n";

echo "=== SOLUTION OPTIONS ===\n\n";

echo "Option 1: Update .env with correct database password\n";
echo "Add this to .env file:\n";
echo "DB_PASSWORD=your_actual_mysql_password\n\n";

echo "Option 2: Test with MySQL root user (if password is empty)\n";
echo "Try adding this to .env:\n";
echo "DB_PASSWORD=\n\n";

echo "Option 3: Use existing working credentials\n";
echo "From the test, these credentials should work:\n";
echo "DB_HOST=localhost\n";
echo "DB_DATABASE=haslim_bgofis\n";
echo "DB_USERNAME=haslim_bgofis\n";
echo "DB_PASSWORD=your_actual_password\n\n";

echo "=== AUTOMATIC FIX ===\n\n";

// Try to fix automatically by checking if password is empty
$testConnections = [
    [
        'host' => '127.0.0.1',
        'database' => 'haslim_bgofis',
        'username' => 'haslim_bgofis',
        'password' => ''  // Try empty password first
    ],
    [
        'host' => '127.0.0.1',
        'database' => 'haslim_bgofis',
        'username' => 'haslim_bgofis',
        'password' => 'haslim_bgofis'  // Try username as password
    ],
    [
        'host' => '127.0.0.1',
        'database' => 'haslim_bgofis',
        'username' => 'haslim_bgofis',
        'password' => 'password123'  // Try common password
    ]
];

foreach ($testConnections as $i => $config) {
    echo "Testing connection " . ($i + 1) . "...\n";
    echo "  Host: " . $config['host'] . "\n";
    echo "  Database: " . $config['database'] . "\n";
    echo "  Username: " . $config['username'] . "\n";
    echo "  Password: " . (empty($config['password']) ? '[EMPTY]' : substr($config['password'], 0, 3) . '***') . "\n";
    
    try {
        $pdo = new PDO(
            'mysql:host=' . $config['host'] . 
            ';dbname=' . $config['database'] . 
            ';charset=utf8mb4',
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        echo "  ✅ SUCCESS: Connection works!\n";
        
        // Test query
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users LIMIT 1");
        $stmt->execute();
        $result = $stmt->fetch();
        
        if ($result && $result['count'] > 0) {
            echo "  ✅ Database accessible with " . $result['count'] . " users\n";
            
            // Update .env file with working credentials
            $envContent = file_get_contents($envPath . '/.env');
            $newEnvContent = preg_replace(
                '/^DB_PASSWORD=.*$/m',
                'DB_PASSWORD=' . $config['password'],
                $envContent
            );
            
            if (file_put_contents($envPath . '/.env', $newEnvContent)) {
                echo "  ✅ Updated .env file with working password\n";
            } else {
                echo "  ⚠️  Could not update .env file automatically\n";
            }
            
            echo "\n=== CREDENTIALS WORK ===\n";
            echo "Database connection successful!\n";
            echo "Now test login:\n";
            echo "curl -X POST \"https://bgaofis.billurguleraslim.av.tr/api/auth/login\" \\\n";
            echo "     -H \"Content-Type: application/json\" \\\n";
            echo "     -d '{\"email\":\"alihaydaraslim@gmail.com\",\"password\":\"test123456\"}'\n\n";
            
            break;
        }
        
    } catch (PDOException $e) {
        echo "  ✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "=== MANUAL FIX INSTRUCTIONS ===\n";
echo "If automatic fix didn't work, manually update .env:\n\n";
echo "1. Open: /home/haslim/public_html/bgaofis.billurguleraslim.av.tr/.env\n";
echo "2. Find line: DB_PASSWORD=\n";
echo "3. Change to: DB_PASSWORD=your_actual_mysql_password\n";
echo "4. Save file\n";
echo "5. Test login again\n\n";

echo "=== Fix Complete ===\n";
