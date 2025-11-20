<?php

echo "=== COMPLETE ROLE LOADING FIX ===\n\n";

// Database configuration - adjust these values based on your setup
$db_config = [
    'host' => 'localhost',
    'dbname' => 'hukuk_otomasyon', // Adjust if your database name is different
    'username' => 'root',
    'password' => ''
];

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['dbname']};charset=utf8mb4",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "✅ Database connected successfully\n\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in this script.\n";
    exit(1);
}

// Step 1: Check if user exists
echo "Step 1: Checking user existence...\n";
$stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = 22 OR email = 'alihaydaraslim@gmail.com'");
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    echo "❌ User not found! Please check the user ID and email.\n";
    exit(1);
}

echo "✅ User found:\n";
echo "   ID: {$user['id']}\n";
echo "   Name: {$user['name']}\n";
echo "   Email: {$user['email']}\n\n";
$userId = $user['id'];

// Step 2: Check if administrator role exists
echo "Step 2: Checking administrator role...\n";
$stmt = $pdo->prepare("SELECT id, name, `key` FROM roles WHERE `key` = 'administrator'");
$stmt->execute();
$adminRole = $stmt->fetch();

if (!$adminRole) {
    echo "❌ Administrator role not found! Creating it...\n";
    $stmt = $pdo->prepare("INSERT INTO roles (name, `key`, description, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $stmt->execute(['Administrator', 'administrator', 'System administrator with full access']);
    
    $adminRoleId = $pdo->lastInsertId();
    echo "✅ Administrator role created with ID: $adminRoleId\n";
} else {
    echo "✅ Administrator role found:\n";
    echo "   ID: {$adminRole['id']}\n";
    echo "   Name: {$adminRole['name']}\n";
    echo "   Key: {$adminRole['key']}\n";
    $adminRoleId = $adminRole['id'];
}

// Step 3: Check current user roles
echo "\nStep 3: Checking current user roles...\n";
$stmt = $pdo->prepare("
    SELECT ur.role_id, r.name, r.`key` 
    FROM user_roles ur 
    JOIN roles r ON ur.role_id = r.id 
    WHERE ur.user_id = ?
");
$stmt->execute([$userId]);
$currentRoles = $stmt->fetchAll();

echo "Current roles assigned: " . count($currentRoles) . "\n";
foreach ($currentRoles as $role) {
    echo "   - Role ID: {$role['role_id']}, Name: {$role['name']}, Key: {$role['key']}\n";
}

// Step 4: Assign administrator role if not already assigned
$hasAdminRole = false;
foreach ($currentRoles as $role) {
    if ($role['key'] === 'administrator') {
        $hasAdminRole = true;
        break;
    }
}

if (!$hasAdminRole) {
    echo "\nStep 4: Assigning administrator role to user...\n";
    $stmt = $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute([$userId, $adminRoleId]);
    echo "✅ Administrator role assigned to user!\n";
} else {
    echo "\nStep 4: User already has administrator role! ✅\n";
}

// Step 5: Check menu permissions
echo "\nStep 5: Checking menu permissions...\n";
$stmt = $pdo->prepare("
    SELECT mp.menu_item_id, mi.title, mi.route, mp.can_view, mp.can_create, mp.can_edit, mp.can_delete
    FROM menu_permissions mp
    JOIN menu_items mi ON mp.menu_item_id = mi.id
    WHERE mp.user_id = ?
");
$stmt->execute([$userId]);
$menuPermissions = $stmt->fetchAll();

echo "Current menu permissions: " . count($menuPermissions) . "\n";
foreach ($menuPermissions as $permission) {
    echo "   - {$permission['title']} ({$permission['route']}): View={$permission['can_view']}, Create={$permission['can_create']}, Edit={$permission['can_edit']}, Delete={$permission['can_delete']}\n";
}

// Step 6: Grant full menu permissions to administrator
echo "\nStep 6: Granting full menu permissions to administrator...\n";
$stmt = $pdo->prepare("
    INSERT IGNORE INTO menu_permissions (user_id, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at)
    SELECT ?, mi.id, 1, 1, 1, 1, NOW(), NOW()
    FROM menu_items mi
    WHERE mi.id NOT IN (
        SELECT menu_item_id FROM menu_permissions WHERE user_id = ?
    )
");
$stmt->execute([$userId, $userId]);
$affectedRows = $stmt->rowCount();
echo "✅ Granted permissions to $affectedRows menu items\n";

// Step 7: Final verification
echo "\nStep 7: Final verification...\n";
$stmt = $pdo->prepare("
    SELECT ur.role_id, r.name, r.`key` 
    FROM user_roles ur 
    JOIN roles r ON ur.role_id = r.id 
    WHERE ur.user_id = ?
");
$stmt->execute([$userId]);
$finalRoles = $stmt->fetchAll();

echo "Final role count: " . count($finalRoles) . "\n";
foreach ($finalRoles as $role) {
    echo "   - Role ID: {$role['role_id']}, Name: {$role['name']}, Key: {$role['key']}\n";
}

echo "\n=== ROLE FIXING COMPLETE ===\n";
echo "\nNext steps:\n";
echo "1. Refresh your browser to reload the frontend\n";
echo "2. The ProfileLoader component will automatically fetch the updated user data\n";
echo "3. You should now see the administrator role in your user profile\n";
echo "4. Menu management access should be working\n";
echo "\nIf issues persist, check the browser console for any JavaScript errors.\n";