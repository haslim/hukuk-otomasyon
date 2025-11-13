<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowTemplate extends BaseModel
{
    protected $table = 'workflow_templates';

    protected $casts = [
        'tags' => 'array'
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class, 'template_id');
    }
}
