<?php
// Diagnostic script to debug menu access control issue
require_once 'backend/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/backend');
$dotenv->load();

// Bootstrap the application
require_once 'backend/bootstrap/app.php';

use App\Models\User;
use App\Services\AuthService;
use App\Support\AuthContext;

echo "=== Menu Access Control Debug ===\n\n";

// Check 1: Test if we can find users with administrator role
echo "1. Checking for administrator users:\n";
$adminUsers = User::whereHas('roles', function($query) {
    $query->where('key', 'administrator');
})->with('roles')->get();

if ($adminUsers->count() > 0) {
    echo "Found {$adminUsers->count()} administrator user(s):\n";
    foreach ($adminUsers as $user) {
        echo "  - ID: {$user->id}, Email: {$user->email}, Roles: " . json_encode($user->roles->pluck('key')->toArray()) . "\n";
    }
} else {
    echo "No administrator users found!\n";
    
    // Check all users and their roles
    echo "\nAll users and their roles:\n";
    $allUsers = User::with('roles')->get();
    foreach ($allUsers as $user) {
        echo "  - ID: {$user->id}, Email: {$user->email}, Roles: " . json_encode($user->roles->pluck('key')->toArray()) . "\n";
    }
}

// Check 2: Test role permissions
echo "\n2. Checking role permissions for USER_MANAGE:\n";
$rolesWithPermission = \App\Models\Role::whereHas('permissions', function($query) {
    $query->where('key', 'USER_MANAGE');
})->with('permissions')->get();

if ($rolesWithPermission->count() > 0) {
    echo "Found {$rolesWithPermission->count()} role(s) with USER_MANAGE permission:\n";
    foreach ($rolesWithPermission as $role) {
        echo "  - Role: {$role->name} ({$role->key}), Permissions: " . json_encode($role->permissions->pluck('key')->toArray()) . "\n";
    }
} else {
    echo "No roles found with USER_MANAGE permission!\n";
    
    // Check all roles and permissions
    echo "\nAll roles and their permissions:\n";
    $allRoles = \App\Models\Role::with('permissions')->get();
    foreach ($allRoles as $role) {
        echo "  - Role: {$role->name} ({$role->key}), Permissions: " . json_encode($role->permissions->pluck('key')->toArray()) . "\n";
    }
}

// Check 3: Test User hasPermission method
echo "\n3. Testing User::hasPermission method:\n";
$testUser = $adminUsers->first() ?: User::first();
if ($testUser) {
    echo "Testing user: {$testUser->email} (ID: {$testUser->id})\n";
    
    // Test with administrator role check
    $hasUserManage = $testUser->hasPermission('USER_MANAGE');
    echo "  - hasPermission('USER_MANAGE'): " . ($hasUserManage ? 'true' : 'false') . "\n";
    
    // Debug the hasPermission logic
    $roles = $testUser->roles()->with('permissions')->get();
    echo "  - User roles: " . json_encode($roles->pluck('key')->toArray()) . "\n";
    echo "  - Has administrator role: " . ($roles->pluck('key')->contains('administrator') ? 'true' : 'false') . "\n";
    
    $rolePermissions = $roles
        ->flatMap(fn ($role) => $role->permissions)
        ->pluck('key')
        ->all();
    echo "  - Role permissions: " . json_encode($rolePermissions) . "\n";
    
    $tokenPermissions = (array) ($testUser->getAttribute('token_permissions') ?? []);
    echo "  - Token permissions: " . json_encode($tokenPermissions) . "\n";
}

// Check 4: Test AuthMiddleware and RoleMiddleware flow
echo "\n4. Testing authentication flow:\n";
if ($testUser) {
    // Simulate token creation
    $authService = new AuthService();
    $testPassword = 'password'; // This would need to match actual user password
    
    echo "  - Testing AuthService::attempt for user {$testUser->email}\n";
    // Note: This will fail unless we know the actual password
    // $loginResult = $authService->attempt($testUser->email, $testPassword);
    // if ($loginResult) {
    //     echo "    Login successful\n";
    //     $token = $loginResult['token'];
    //     echo "    Token permissions: " . json_encode($loginResult['user']->roles()->with('permissions')->get()
    //         ->flatMap(fn ($role) => $role->permissions->pluck('key'))
    //         ->unique()
    //         ->values()
    //         ->toArray()) . "\n";
    // } else {
    //     echo "    Login failed (expected if password doesn't match)\n";
    // }
}

echo "\n=== Debug Complete ===\n";