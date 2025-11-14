<?php

namespace App\Repositories;

use App\Models\AuditLog;

class AuditRepository extends BaseRepository
{
    public function __construct(AuditLog $model)
    {
        parent::__construct($model);
    }
}
