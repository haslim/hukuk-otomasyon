<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends BaseModel
{
    protected $table = 'menu_items';

    protected $fillable = [
        'path',
        'label',
        'icon',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function menuPermissions(): HasMany
    {
        return $this->hasMany(MenuPermission::class, 'menu_item_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'menu_permissions', 'menu_item_id', 'role_id')
            ->withPivot('is_visible')
            ->withTimestamps();
    }

    public function isVisibleForRole(string $roleId): bool
    {
        return $this->menuPermissions()
            ->where('role_id', $roleId)
            ->where('is_visible', true)
            ->exists();
    }
}
