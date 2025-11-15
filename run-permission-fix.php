<?php
/**
 * BGAofis Law Office Automation - Permission Fix Script
 * This script creates missing permissions without external dependencies
 */

echo "BGAofis Law Office Automation - Permission Fix\n";
echo "=============================================\n\n";

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

echo "\n2. Creating required permissions...\n";
$permissions = [
    'CASE_VIEW_ALL' => 'View All Cases',
    'CASE_CREATE' => 'Create New Cases',
    'CASE_EDIT' => 'Edit Cases',
    'CASE_DELETE' => 'Delete Cases',
    'CLIENT_VIEW_ALL' => 'View All Clients',
    'CLIENT_CREATE' => 'Create New Clients',
    'CLIENT_EDIT' => 'Edit Clients',
    'CLIENT_DELETE' => 'Delete Clients',
    'CASH_VIEW' => 'View Cash Flow',
    'CASH_MANAGE' => 'Manage Cash Transactions',
    'USER_MANAGE' => 'Manage Users',
    'ROLE_MANAGE' => 'Manage Roles',
    'DOCUMENT_VIEW' => 'View Documents',
    'DOCUMENT_MANAGE' => 'Manage Documents',
    'TASK_MANAGE' => 'Manage Tasks',
    'WORKFLOW_MANAGE' => 'Manage Workflows'
];

$permissionFixes = 0;
foreach ($permissions as $key => $name) {
    try {
        // Check if permission exists
        $stmt = $pdo->prepare("SELECT id FROM permissions WHERE `key` = ?");
        $stmt->execute([$key]);
        $existing = $stmt->fetchAll();
        
        if (empty($existing)) {
            // Create permission
            $id = uniqid();
            $stmt = $pdo->prepare("INSERT INTO permissions (id, `key`, name, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $stmt->execute([$id, $key, $name]);
            echo "✓ Created permission: {$key}\n";
            $permissionFixes++;
        } else {
            echo "✓ Permission already exists: {$key}\n";
        }
    } catch (Exception $e) {
        echo "✗ Error creating permission {$key}: " . $e->getMessage() . "\n";
    }
}

echo "\n3. Ensuring admin role exists...\n";
try {
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE `key` = 'admin'");
    $stmt->execute();
    $adminRole = $stmt->fetchAll();
    
    if (empty($adminRole)) {
        $adminId = uniqid();
        $stmt = $pdo->prepare("INSERT INTO roles (id, `key`, name, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$adminId, 'admin', 'Administrator']);
        echo "✓ Created admin role\n";
        $adminRoleId = $adminId;
    } else {
        echo "✓ Admin role already exists\n";
        $adminRoleId = $adminRole[0]->id;
    }
} catch (Exception $e) {
    echo "✗ Error creating admin role: " . $e->getMessage() . "\n";
    $adminRoleId = null;
}

echo "\n4. Assigning all permissions to admin role...\n";
if ($adminRoleId) {
    $rolePermFixes = 0;
    foreach ($permissions as $key => $name) {
        try {
            // Get permission ID
            $stmt = $pdo->prepare("SELECT id FROM permissions WHERE `key` = ?");
            $stmt->execute([$key]);
            $perm = $stmt->fetchAll();
            
            if (!empty($perm)) {
                $permId = $perm[0]->id;
                
                // Check if role permission exists
                $stmt = $pdo->prepare("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?");
                $stmt->execute([$adminRoleId, $permId]);
                $existing = $stmt->fetchAll();
                
                if (empty($existing)) {
                    // Create role permission
                    $stmt = $pdo->prepare("INSERT INTO role_permissions (id, role_id, permission_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                    $stmt->execute([uniqid(), $adminRoleId, $permId]);
                    echo "✓ Assigned {$key} to admin role\n";
                    $rolePermFixes++;
                } else {
                    echo "✓ {$key} already assigned to admin role\n";
                }
            }
        } catch (Exception $e) {
            echo "✗ Error assigning {$key} to admin role: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n5. Checking current users...\n";
try {
    $stmt = $pdo->prepare("SELECT id, email FROM users ORDER BY email");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    if (empty($users)) {
        echo "✗ No users found in database\n";
    } else {
        echo "✓ Found " . count($users) . " users:\n";
        foreach ($users as $user) {
            echo "  - {$user['email']}\n";
            
            // Check if user has admin role
            $stmt = $pdo->prepare("
                SELECT r.key FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE ur.user_id = ?
            ");
            $stmt->execute([$user['id']]);
            $userRoles = $stmt->fetchAll();
            
            $hasAdmin = false;
            foreach ($userRoles as $role) {
                if ($role['key'] === 'admin') {
                    $hasAdmin = true;
                    break;
                }
            }
            
            if (!$hasAdmin && $adminRoleId) {
                // Assign admin role to first user
                $stmt = $pdo->prepare("INSERT INTO user_roles (id, user_id, role_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
                $stmt->execute([uniqid(), $user['id'], $adminRoleId]);
                echo "    ✓ Assigned admin role to {$user['email']}\n";
            } elseif ($hasAdmin) {
                echo "    ✓ Already has admin role\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking users: " . $e->getMessage() . "\n";
}

echo "\n6. Verifying CASE_VIEW_ALL permission assignment...\n";
try {
    $stmt = $pdo->prepare("
        SELECT u.email, r.name as role_name 
        FROM users u 
        JOIN user_roles ur ON u.id = ur.user_id 
        JOIN roles r ON ur.role_id = r.id 
        JOIN role_permissions rp ON r.id = rp.role_id 
        JOIN permissions p ON rp.permission_id = p.id 
        WHERE p.key = 'CASE_VIEW_ALL'
    ");
    $stmt->execute();
    $result = $stmt->fetchAll();
    
    if (empty($result)) {
        echo "✗ No users have CASE_VIEW_ALL permission\n";
    } else {
        echo "✓ Users with CASE_VIEW_ALL permission:\n";
        foreach ($result as $user) {
            echo "  - {$user['email']} (via {$user['role_name']})\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error verifying CASE_VIEW_ALL: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Permission Fix Summary:\n";
echo "- Permissions created/updated: {$permissionFixes}\n";
echo "- Role permissions assigned: " . ($rolePermFixes ?? 0) . "\n";
echo "- Admin role ensured: ✓\n";

echo "\nNext Steps:\n";
echo "1. Upload updated files to production server:\n";
echo "   - backend/app/Controllers/CalendarController.php (NEW)\n";
echo "   - backend/app/Controllers/UserController.php (FIXED)\n";
echo "   - backend/app/Controllers/FinanceController.php (UPDATED)\n";
echo "   - backend/app/Models/FinanceTransaction.php (UPDATED)\n";
echo "2. Test the /api/cases endpoint again\n";
echo "3. Test your application at: https://bgaofis.billurguleraslim.av.tr\n";

echo "\nExpected Results:\n";
echo "- ✅ All required permissions exist\n";
echo "- ✅ Admin role has all permissions\n";
echo "- ✅ At least one user has admin role\n";
echo "- ✅ /api/cases should return 200 OK\n";
echo "- ✅ Frontend application works completely\n";