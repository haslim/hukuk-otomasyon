<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends BaseModel
{
    protected $table = 'users';
    protected $hidden = ['password'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function hasPermission(string $permission): bool
    {
        $roles = $this->roles()->with('permissions')->get();

        // Administrator rolüne sahip olan herkes tüm izinlere sahiptir
        if ($roles->pluck('key')->contains('administrator')) {
            return true;
        }

        $rolePermissions = $roles
            ->flatMap(fn ($role) => $role->permissions)
            ->pluck('key')
            ->all();

        $tokenPermissions = (array) ($this->getAttribute('token_permissions') ?? []);

        foreach (array_unique(array_merge($rolePermissions, $tokenPermissions)) as $key) {
            if ($key === '*' || $key === $permission) {
                return true;
            }
        }

        return false;
    }
}
