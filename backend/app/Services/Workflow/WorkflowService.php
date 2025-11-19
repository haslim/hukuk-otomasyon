<?php

namespace App\Services\Workflow;

use App\Models\WorkflowStep;
use App\Repositories\WorkflowRepository;
use InvalidArgumentException;

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

    public function createTemplate(array $data)
    {
        $name = trim((string) ($data['name'] ?? ''));
        $caseType = trim((string) ($data['case_type'] ?? ''));

        if ($name === '' || $caseType === '') {
            throw new InvalidArgumentException('Workflow template name and case type are required.');
        }

        $rawSteps = $data['steps'] ?? [];
        $steps = [];
        $hasOrderColumn = WorkflowStep::hasOrderColumn();

        foreach ($rawSteps as $step) {
            $title = trim((string) ($step['title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $stepPayload = [
                'title' => $title,
                'is_required' => isset($step['is_required']) ? (bool) $step['is_required'] : true,
            ];

            if ($hasOrderColumn) {
                $stepPayload['order'] = count($steps) + 1;
            }

            $steps[] = $stepPayload;
        }

        if (count($steps) === 0) {
            throw new InvalidArgumentException('At least one workflow step is required.');
        }

        $template = $this->templates->create([
            'name' => $name,
            'case_type' => $caseType,
            'tags' => $data['tags'] ?? null,
        ]);

        foreach ($steps as $step) {
            $step['template_id'] = $template->id;
            WorkflowStep::create($step);
        }

        return $template->load('steps');
    }
}
