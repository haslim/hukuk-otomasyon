<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;

class Role extends BaseModel
{
    protected $table = 'roles';

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    protected function getKeyName()
    {
        $schema = Schema::getFacadeApplication()->make('db')->getSchemaBuilder();
        if ($schema->hasColumn($this->table, 'key')) {
            return 'id';
        }

        return parent::getKeyName();
    }
}
