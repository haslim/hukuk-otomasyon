<?php
/**
 * BGAofis Law Office Automation - Permission Check Script
 * This script checks the current permissions and roles in the database
 */

echo "BGAofis Law Office Automation - Permission Check\n";
echo "===============================================\n\n";

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

echo "\n2. Checking database tables...\n";
$tables = ['users', 'roles', 'permissions', 'user_roles', 'role_permissions'];

foreach ($tables as $table) {
    $result = $connection->select("SHOW TABLES LIKE '$table'");
    if (!empty($result)) {
        echo "✓ Table '$table' exists\n";
    } else {
        echo "✗ Table '$table' missing\n";
    }
}

echo "\n3. Checking permissions...\n";
try {
    $permissions = $connection->select("SELECT * FROM permissions ORDER BY `key`");
    if (empty($permissions)) {
        echo "✗ No permissions found in database\n";
    } else {
        echo "✓ Found " . count($permissions) . " permissions:\n";
        foreach ($permissions as $perm) {
            echo "  - {$perm->key}: {$perm->name}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking permissions: " . $e->getMessage() . "\n";
}

echo "\n4. Checking roles...\n";
try {
    $roles = $connection->select("SELECT * FROM roles ORDER BY name");
    if (empty($roles)) {
        echo "✗ No roles found in database\n";
    } else {
        echo "✓ Found " . count($roles) . " roles:\n";
        foreach ($roles as $role) {
            echo "  - {$role->name} ({$role->key})\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking roles: " . $e->getMessage() . "\n";
}

echo "\n5. Checking role permissions...\n";
try {
    $rolePerms = $connection->select("
        SELECT r.name as role_name, p.key as permission_key 
        FROM role_permissions rp 
        JOIN roles r ON rp.role_id = r.id 
        JOIN permissions p ON rp.permission_id = p.id 
        ORDER BY r.name, p.key
    ");
    
    if (empty($rolePerms)) {
        echo "✗ No role permissions found\n";
    } else {
        echo "✓ Found role permissions:\n";
        $currentRole = '';
        foreach ($rolePerms as $rp) {
            if ($rp->role_name !== $currentRole) {
                $currentRole = $rp->role_name;
                echo "  {$currentRole}:\n";
            }
            echo "    - {$rp->permission_key}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking role permissions: " . $e->getMessage() . "\n";
}

echo "\n6. Checking user roles...\n";
try {
    $userRoles = $connection->select("
        SELECT u.email, r.name as role_name 
        FROM user_roles ur 
        JOIN users u ON ur.user_id = u.id 
        JOIN roles r ON ur.role_id = r.id 
        ORDER BY u.email, r.name
    ");
    
    if (empty($userRoles)) {
        echo "✗ No user roles found\n";
    } else {
        echo "✓ Found user roles:\n";
        $currentUser = '';
        foreach ($userRoles as $ur) {
            if ($ur->email !== $currentUser) {
                $currentUser = $ur->email;
                echo "  {$currentUser}:\n";
            }
            echo "    - {$ur->role_name}\n";
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking user roles: " . $e->getMessage() . "\n";
}

echo "\n7. Checking for CASE_VIEW_ALL permission specifically...\n";
try {
    $caseViewPerm = $connection->select("SELECT * FROM permissions WHERE `key` = 'CASE_VIEW_ALL'");
    if (empty($caseViewPerm)) {
        echo "✗ CASE_VIEW_ALL permission not found\n";
        echo "  This permission is required for accessing /api/cases endpoint\n";
    } else {
        echo "✓ CASE_VIEW_ALL permission exists\n";
        
        // Check which roles have this permission
        $rolesWithPerm = $connection->select("
            SELECT r.name, r.key 
            FROM role_permissions rp 
            JOIN roles r ON rp.role_id = r.id 
            WHERE rp.permission_id = (SELECT id FROM permissions WHERE `key` = 'CASE_VIEW_ALL')
        ");
        
        if (empty($rolesWithPerm)) {
            echo "✗ No roles have CASE_VIEW_ALL permission\n";
        } else {
            echo "✓ Roles with CASE_VIEW_ALL permission:\n";
            foreach ($rolesWithPerm as $role) {
                echo "  - {$role->name} ({$role->key})\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Error checking CASE_VIEW_ALL permission: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Permission Check Complete\n";
echo "\nIf CASE_VIEW_ALL permission is missing or not assigned:\n";
echo "1. Create the permission in the permissions table\n";
echo "2. Assign it to appropriate roles (e.g., Admin, Lawyer)\n";
echo "3. Ensure users have those roles\n";