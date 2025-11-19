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
        $relation = $this->hasMany(WorkflowStep::class, 'template_id');
        if (WorkflowStep::hasOrderColumn()) {
            return $relation->orderBy('order');
        }
        return $relation->orderBy('created_at');
    }
}
