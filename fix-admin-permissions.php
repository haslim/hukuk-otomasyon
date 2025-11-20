<?php
// Script to fix administrator role permissions
require_once 'backend/bootstrap/app.php';

use App\Models\Role;
use App\Models\Permission;

echo "=== Fixing Administrator Role Permissions ===\n\n";

// Find administrator role
$adminRole = Role::where('key', 'administrator')->first();
if (!$adminRole) {
    echo "ERROR: Administrator role not found!\n";
    exit(1);
}

echo "Found administrator role: {$adminRole->name} (ID: {$adminRole->id})\n";

// Find or create USER_MANAGE permission
$userManagePermission = Permission::where('key', 'USER_MANAGE')->first();
if (!$userManagePermission) {
    echo "Creating USER_MANAGE permission...\n";
    $userManagePermission = new Permission();
    $userManagePermission->key = 'USER_MANAGE';
    $userManagePermission->name = 'User Management';
    $userManagePermission->description = 'Can manage users and roles';
    $userManagePermission->save();
    echo "Created USER_MANAGE permission (ID: {$userManagePermission->id})\n";
} else {
    echo "Found USER_MANAGE permission: {$userManagePermission->name} (ID: {$userManagePermission->id})\n";
}

// Check if administrator role already has USER_MANAGE permission
$hasPermission = $adminRole->permissions()->where('permission_id', $userManagePermission->id)->exists();
if ($hasPermission) {
    echo "Administrator role already has USER_MANAGE permission.\n";
} else {
    echo "Adding USER_MANAGE permission to administrator role...\n";
    $adminRole->permissions()->attach($userManagePermission->id);
    echo "USER_MANAGE permission added to administrator role.\n";
}

// Display all permissions for administrator role
$allPermissions = $adminRole->permissions()->get();
echo "\nAdministrator role permissions:\n";
foreach ($allPermissions as $permission) {
    echo "  - {$permission->key}: {$permission->name}\n";
}

echo "\n=== Fix Complete ===\n";