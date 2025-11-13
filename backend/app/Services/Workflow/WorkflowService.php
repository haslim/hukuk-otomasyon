<?php

namespace App\Services\Workflow;

use App\Repositories\WorkflowRepository;

class WorkflowService
{
    public function __construct(private readonly WorkflowRepository $templates)
    {
    }

    public function templates()
    {
        return $this->templates->all();
    }

    public function instantiateFromTemplate(string $templateId): array
    {
        $template = $this->templates->find($templateId);
        return $template->steps->map(fn ($step) => [
            'step_id' => $step->id,
            'title' => $step->title,
            'is_required' => $step->is_required,
            'status' => 'pending'
        ])->toArray();
    }
}
