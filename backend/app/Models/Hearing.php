<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}
