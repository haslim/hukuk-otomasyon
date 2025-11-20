<?php

require_once 'backend/bootstrap/app.php';

use App\Models\User;
use App\Models\Role;

echo "=== USER ROLE VERIFICATION SCRIPT ===\n\n";

// Check if user ID 22 exists
$user = User::find(22);
if (!$user) {
    echo "❌ User with ID 22 not found!\n";
    exit(1);
}

echo "✅ User found:\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n\n";

// Check current roles
$roles = $user->roles;
echo "Current roles assigned: " . $roles->count() . "\n";

if ($roles->count() > 0) {
    foreach ($roles as $role) {
        echo "   - Role ID: {$role->id}, Name: {$role->name}, Key: {$role->key}\n";
    }
} else {
    echo "   No roles assigned!\n";
}

// Check if administrator role exists
$adminRole = Role::where('key', 'administrator')->first();
if (!$adminRole) {
    echo "\n❌ Administrator role not found!\n";
    
    // Create administrator role
    echo "Creating administrator role...\n";
    $adminRole = new Role();
    $adminRole->name = 'Administrator';
    $adminRole->key = 'administrator';
    $adminRole->description = 'System administrator with full access';
    $adminRole->save();
    echo "✅ Administrator role created with ID: {$adminRole->id}\n";
} else {
    echo "\n✅ Administrator role found:\n";
    echo "   ID: {$adminRole->id}\n";
    echo "   Name: {$adminRole->name}\n";
    echo "   Key: {$adminRole->key}\n";
}

// Check if user has administrator role
$hasAdminRole = $user->roles()->where('key', 'administrator')->exists();
if (!$hasAdminRole) {
    echo "\n❌ User does NOT have administrator role!\n";
    
    // Assign administrator role to user
    echo "Assigning administrator role to user...\n";
    $user->roles()->attach($adminRole->id);
    echo "✅ Administrator role assigned to user!\n";
} else {
    echo "\n✅ User already has administrator role!\n";
}

// Verify final state
$user->refresh(); // Refresh to get updated relationships
$finalRoles = $user->roles;
echo "\nFinal role count: " . $finalRoles->count() . "\n";
foreach ($finalRoles as $role) {
    echo "   - Role ID: {$role->id}, Name: {$role->name}, Key: {$role->key}\n";
}

echo "\n=== VERIFICATION COMPLETE ===\n";