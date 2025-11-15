<?php

namespace App\Models;

use App\Models\CaseModel;

class Hearing extends BaseModel
{
    protected $table = 'hearings';

    protected $fillable = [
        'case_id',
        'hearing_date',
        'court',
        'notes'
    ];

    protected $casts = [
        'hearing_date' => 'datetime',
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}
