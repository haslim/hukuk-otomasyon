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
        'is_active',
        'parent_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'string'
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

    public function parent()
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }

    public function isVisibleForRole(string $roleId): bool
    {
        return $this->menuPermissions()
            ->where('role_id', $roleId)
            ->where('is_visible', true)
            ->exists();
    }
}
