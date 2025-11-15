<?php

namespace App\Models;

class Client extends BaseModel
{
    protected $table = 'clients';

    protected $fillable = [
        'name',
        'type',
        'identifier',
        'phone',
        'email',
        'labels',
        'notes'
    ];

    protected $casts = [
        'labels' => 'array'
    ];

    public function cases()
    {
        return $this->hasMany(CaseModel::class, 'client_id');
    }
}
