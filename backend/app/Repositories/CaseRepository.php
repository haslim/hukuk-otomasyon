<?php

namespace App\Repositories;

use App\Models\CaseModel;

class CaseRepository extends BaseRepository
{
    public function __construct(CaseModel $model)
    {
        parent::__construct($model);
    }

    public function attachWorkflow(string $caseId, array $workflow): CaseModel
    {
        $case = $this->find($caseId);
        $case->metadata = array_merge($case->metadata ?? [], ['workflow' => $workflow]);
        $case->save();
        return $case;
    }
}
