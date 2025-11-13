<?php

namespace App\Repositories;

use App\Models\WorkflowTemplate;

class WorkflowRepository extends BaseRepository
{
    public function __construct(WorkflowTemplate $model)
    {
        parent::__construct($model);
    }

    public function all(array $filters = [])
    {
        return $this->model->newQuery()->where($filters)->with('steps')->orderBy('case_type')->get();
    }
}
