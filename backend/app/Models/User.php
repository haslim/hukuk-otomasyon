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
        $rolePermissions = $this->roles()->with('permissions')->get()
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
