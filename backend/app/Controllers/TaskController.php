<?php

namespace App\Controllers;

use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TaskController extends Controller
{
    private TaskService $tasks;

    public function __construct()
    {
        $this->tasks = new TaskService(new TaskRepository(new Task()));
    }

    public function index(Request $request, Response $response): Response
    {
        $result = $this->tasks->list($request->getQueryParams());
        return $this->json($response, $result->toArray());
    }

    public function store(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $task = $this->tasks->create($payload);
        return $this->json($response, $task->toArray(), 201);
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $payload = (array) $request->getParsedBody();
        $task = $this->tasks->update($args['id'], $payload);
        return $this->json($response, $task->toArray());
    }
}
