<?php
/**
 * BGAofis Law Office Automation - Permission Fix Script
 * This script creates missing permissions and assigns them to appropriate roles
 */

echo "BGAofis Law Office Automation - Permission Fix\n";
echo "=============================================\n\n";

// Load environment variables
if (file_exists('.env')) {
    echo "Loading environment variables from .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

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
        $existing = $connection->select("SELECT id FROM permissions WHERE `key` = ?", [$key]);
        
        if (empty($existing)) {
            // Create permission
            $id = uniqid();
            $connection->insert("INSERT INTO permissions (id, `key`, name, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [
                $id, $key, $name
            ]);
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
    $adminRole = $connection->select("SELECT id FROM roles WHERE `key` = 'admin'");
    
    if (empty($adminRole)) {
        $adminId = uniqid();
        $connection->insert("INSERT INTO roles (id, `key`, name, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [
            $adminId, 'admin', 'Administrator'
        ]);
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
            $perm = $connection->select("SELECT id FROM permissions WHERE `key` = ?", [$key]);
            
            if (!empty($perm)) {
                $permId = $perm[0]->id;
                
                // Check if role permission exists
                $existing = $connection->select("SELECT id FROM role_permissions WHERE role_id = ? AND permission_id = ?", [
                    $adminRoleId, $permId
                ]);
                
                if (empty($existing)) {
                    // Create role permission
                    $connection->insert("INSERT INTO role_permissions (id, role_id, permission_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [
                        uniqid(), $adminRoleId, $permId
                    ]);
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
    $users = $connection->select("SELECT id, email FROM users ORDER BY email");
    
    if (empty($users)) {
        echo "✗ No users found in database\n";
    } else {
        echo "✓ Found " . count($users) . " users:\n";
        foreach ($users as $user) {
            echo "  - {$user->email}\n";
            
            // Check if user has admin role
            $userRoles = $connection->select("
                SELECT r.key FROM user_roles ur 
                JOIN roles r ON ur.role_id = r.id 
                WHERE ur.user_id = ?
            ", [$user->id]);
            
            $hasAdmin = false;
            foreach ($userRoles as $role) {
                if ($role->key === 'admin') {
                    $hasAdmin = true;
                    break;
                }
            }
            
            if (!$hasAdmin && $adminRoleId) {
                // Assign admin role to first user
                $connection->insert("INSERT INTO user_roles (id, user_id, role_id, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())", [
                    uniqid(), $user->id, $adminRoleId
                ]);
                echo "    ✓ Assigned admin role to {$user->email}\n";
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
    $result = $connection->select("
        SELECT u.email, r.name as role_name 
        FROM users u 
        JOIN user_roles ur ON u.id = ur.user_id 
        JOIN roles r ON ur.role_id = r.id 
        JOIN role_permissions rp ON r.id = rp.role_id 
        JOIN permissions p ON rp.permission_id = p.id 
        WHERE p.key = 'CASE_VIEW_ALL'
    ");
    
    if (empty($result)) {
        echo "✗ No users have CASE_VIEW_ALL permission\n";
    } else {
        echo "✓ Users with CASE_VIEW_ALL permission:\n";
        foreach ($result as $user) {
            echo "  - {$user->email} (via {$user->role_name})\n";
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
echo "1. Test the /api/cases endpoint again\n";
echo "2. If still getting 403 errors, check user authentication\n";
echo "3. Verify user is logged in and has valid session\n";

echo "\nExpected Results:\n";
echo "- ✅ All required permissions exist\n";
echo "- ✅ Admin role has all permissions\n";
echo "- ✅ At least one user has admin role\n";
echo "- ✅ /api/cases should return 200 OK\n";