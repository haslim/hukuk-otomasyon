<?php

namespace App\Controllers;

use App\Models\Client;
use App\Repositories\ClientRepository;
use App\Services\ClientService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ClientController extends Controller
{
    private ClientService $service;

    public function __construct()
    {
        $this->service = new ClientService(new ClientRepository(new Client()));
    }

    public function index(Request $request, Response $response): Response
    {
        $data = $this->service->list($request->getQueryParams());
        return $this->json($response, $data->toArray());
    }

    public function store(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $client = $this->service->create($payload);
        return $this->json($response, $client->toArray(), 201);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $client = $this->service->find($args['id']);
        return $this->json($response, $client->toArray());
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $payload = (array) $request->getParsedBody();
        $client = $this->service->update($args['id'], $payload);
        return $this->json($response, $client->toArray());
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $this->service->delete($args['id']);
        return $this->json($response, ['message' => 'Deleted']);
    }
}
