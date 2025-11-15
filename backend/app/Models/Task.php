<?php

namespace App\Models;

use App\Models\CaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends BaseModel
{
    protected $table = 'tasks';

    public function case(): BelongsTo
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }
}
