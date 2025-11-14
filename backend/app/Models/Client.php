<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends BaseModel
{
    protected $table = 'clients';

    public function cases(): HasMany
    {
        return $this->hasMany(CaseModel::class, 'client_id');
    }
}
