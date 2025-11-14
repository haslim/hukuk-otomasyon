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
        return $this->roles()->with('permissions')->get()
            ->flatMap(fn ($role) => $role->permissions)
            ->contains(fn ($perm) => $perm->key === $permission || $perm->key === '*');
    }
}
