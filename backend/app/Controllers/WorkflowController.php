<?php

namespace App\Controllers;

use App\Models\CaseModel;
use App\Models\WorkflowTemplate;
use App\Repositories\CaseRepository;
use App\Repositories\WorkflowRepository;
use App\Services\CaseService;
use App\Services\Workflow\WorkflowService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class WorkflowController extends Controller
{
    private WorkflowService $workflowService;
    private CaseService $caseService;

    public function __construct()
    {
        $workflowRepository = new WorkflowRepository(new WorkflowTemplate());
        $this->workflowService = new WorkflowService($workflowRepository);
        $this->caseService = new CaseService(new CaseRepository(new CaseModel()), $this->workflowService);
    }

    public function templates(Request $request, Response $response): Response
    {
        $templates = $this->workflowService->templates();
        return $this->json($response, $templates->toArray());
    }

    public function attachWorkflow(Request $request, Response $response, array $args): Response
    {
        $payload = (array) $request->getParsedBody();
        $case = $this->caseService->attachWorkflow($args['id'], $payload['template_id']);
        return $this->json($response, $case->toArray());
    }
}
