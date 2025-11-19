<?php

namespace App\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
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

    public static function hasOrderColumn(): bool
    {
        static $exists = null;
        if ($exists === null) {
            $connection = Capsule::connection();
            $exists = $connection->getSchemaBuilder()->hasColumn('workflow_steps', 'order');
        }
        return $exists;
    }
}
