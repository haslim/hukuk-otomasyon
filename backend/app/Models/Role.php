<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends BaseModel
{
    protected $table = 'roles';
    
    protected $fillable = [
        'key',
        'name'
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id');
    }

    public function menuPermissions(): HasMany
    {
        return $this->hasMany(MenuPermission::class, 'role_id');
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_permissions', 'role_id', 'menu_item_id')
            ->withPivot('is_visible')
            ->withTimestamps();
    }
}
