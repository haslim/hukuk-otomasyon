<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends BaseModel
{
    protected $table = 'workflow_steps';

    protected $casts = [
        'is_required' => 'bool'
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'template_id');
    }
}
