<?php

namespace App\Services;

use App\Repositories\CaseRepository;
use App\Services\Workflow\WorkflowService;

class CaseService
{
    public function __construct(
        private readonly CaseRepository $cases,
        private readonly WorkflowService $workflowService
    ) {
    }

    public function list(array $filters = [])
    {
        return $this->cases->all($filters);
    }

    public function find(string $id)
    {
        return $this->cases->find($id);
    }

    public function create(array $data)
    {
        $case = $this->cases->create($data);
        if (!empty($data['workflow_template_id'])) {
            $workflow = $this->workflowService->instantiateFromTemplate($data['workflow_template_id']);
            $this->cases->attachWorkflow($case->id, $workflow);
        }
        return $case;
    }

    public function update(string $id, array $data)
    {
        return $this->cases->update($id, $data);
    }

    public function delete(string $id)
    {
        return $this->cases->delete($id);
    }

    public function attachWorkflow(string $caseId, string $templateId)
    {
        $workflow = $this->workflowService->instantiateFromTemplate($templateId);
        return $this->cases->attachWorkflow($caseId, $workflow);
    }
}
