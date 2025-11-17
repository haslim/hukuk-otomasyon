<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class AdminUserSeeder
{
    private const NAME = 'Ali Haydar Aslim';
    private const EMAIL = 'alihaydaraslim@gmail.com';
    private const PASSWORD = 'test123456';

    public function run(): void
    {
        $hashedPassword = password_hash(self::PASSWORD, PASSWORD_BCRYPT);

        $user = User::withTrashed()->firstOrNew(['email' => self::EMAIL]);

        $user->name = self::NAME;
        $user->password = $hashedPassword;
        // ensure not soft-deleted
        if (isset($user->deleted_at)) {
            $user->deleted_at = null;
        }

        $user->save();

        $permissionDefinitions = [
            '*' => 'Full system access',
            'CASE_VIEW_ALL' => 'View all cases',
            'CASH_VIEW' => 'Access finance data',
            'CALENDAR_MANAGE' => 'Manage calendar events',
            'CLIENT_MANAGE' => 'Manage clients'
        ];

        $permissionIds = [];
        foreach ($permissionDefinitions as $key => $label) {
            $permission = Permission::firstOrCreate(
                ['key' => $key],
                ['name' => $label]
            );
            $permissionIds[] = $permission->id;
        }

        $adminRole = Role::firstOrCreate(
            ['key' => 'administrator'],
            ['name' => 'Administrator']
        );
        $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        $user->roles()->syncWithoutDetaching([$adminRole->id]);

        echo 'Admin role assigned: administrator' . PHP_EOL;

        echo 'Admin user seeded: ' . self::EMAIL . PHP_EOL;
    }
}
