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

class CaseController extends Controller
{
    private CaseService $service;

    public function __construct()
    {
        $this->service = new CaseService(
            new CaseRepository(new CaseModel()),
            new WorkflowService(new WorkflowRepository(new WorkflowTemplate()))
        );
    }

    public function index(Request $request, Response $response): Response
    {
        $cases = $this->service->list($request->getQueryParams());
        return $this->json($response, $cases->toArray());
    }

    public function store(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $case = $this->service->create($payload);
        return $this->json($response, $case->toArray(), 201);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $case = $this->service->find($args['id']);
        return $this->json($response, $case->toArray());
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $payload = (array) $request->getParsedBody();
        $case = $this->service->update($args['id'], $payload);
        return $this->json($response, $case->toArray());
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $this->service->delete($args['id']);
        return $this->json($response, ['message' => 'Removed']);
    }
}
