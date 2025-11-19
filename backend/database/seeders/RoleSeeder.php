<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Capsule\Manager as Capsule;

class RoleSeeder
{
    public function run(): void
    {
        $schema = Capsule::connection()->getSchemaBuilder();
        $permissionHasName = $schema->hasColumn('permissions', 'name');
        $roleHasName = $schema->hasColumn('roles', 'name');

        // Rol tanımlamaları
        $roleDefinitions = [
            'admin' => 'Admin',
            'lawyer' => 'Avukat',
            'accounting' => 'Muhasebe',
            'secretary' => 'Sekreter',
            'intern' => 'Stajyer'
        ];

        // Her rol için yetki tanımlamaları
        $rolePermissions = [
            'admin' => ['*'], // Full access
            'lawyer' => [
                'CASE_VIEW_ALL',
                'CASE_MANAGE',
                'CLIENT_MANAGE',
                'DOCUMENT_MANAGE',
                'CALENDAR_MANAGE',
                'NOTIFICATION_MANAGE'
            ],
            'accounting' => [
                'CASH_VIEW',
                'FINANCE_MANAGE',
                'CLIENT_VIEW',
                'NOTIFICATION_MANAGE'
            ],
            'secretary' => [
                'CLIENT_VIEW',
                'CASE_VIEW_ASSIGNED',
                'DOCUMENT_MANAGE',
                'CALENDAR_MANAGE',
                'NOTIFICATION_MANAGE'
            ],
            'intern' => [
                'CASE_VIEW_ASSIGNED',
                'CLIENT_VIEW',
                'DOCUMENT_VIEW',
                'CALENDAR_VIEW'
            ]
        ];

        // İlk olarak tüm permission'ları oluştur
        $allPermissions = array_unique(array_merge(...array_values($rolePermissions)));
        $permissionMap = [];

        foreach ($allPermissions as $key) {
            $values = [];
            if ($permissionHasName) {
                $values['name'] = $this->getPermissionName($key);
            }
            
            $permission = Permission::firstOrCreate(['key' => $key], $values);
            $permissionMap[$key] = $permission->id;
        }

        // Roller ve yetkilerini oluştur
        foreach ($roleDefinitions as $key => $name) {
            $roleValues = $roleHasName ? ['name' => $name] : [];
            $role = Role::firstOrCreate(['key' => $key], $roleValues);
            
            // Rol için yetkileri ata
            if (isset($rolePermissions[$key])) {
                $permissionIds = array_map(fn($permKey) => $permissionMap[$permKey], $rolePermissions[$key]);
                $role->permissions()->syncWithoutDetaching($permissionIds);
            }

            echo "Role created: {$name} (key: {$key})" . PHP_EOL;
        }

        echo "All roles and permissions seeded successfully." . PHP_EOL;
    }

    private function getPermissionName(string $key): string
    {
        $permissionNames = [
            '*' => 'Full system access',
            'USER_MANAGE' => 'Manage users and roles',
            'CASE_VIEW_ALL' => 'View all cases',
            'CASE_MANAGE' => 'Manage cases',
            'CASE_VIEW_ASSIGNED' => 'View assigned cases',
            'CLIENT_MANAGE' => 'Manage clients',
            'CLIENT_VIEW' => 'View clients',
            'CASH_VIEW' => 'Access finance data',
            'FINANCE_MANAGE' => 'Manage finance',
            'CALENDAR_MANAGE' => 'Manage calendar events',
            'CALENDAR_VIEW' => 'View calendar',
            'DOCUMENT_MANAGE' => 'Manage documents',
            'DOCUMENT_VIEW' => 'View documents',
            'NOTIFICATION_MANAGE' => 'Manage notifications'
        ];

        return $permissionNames[$key] ?? $key;
    }
}
