<?php

namespace App\Controllers;

use App\Models\Document;
use App\Repositories\DocumentRepository;
use App\Services\Documents\DocumentService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DocumentController extends Controller
{
    private DocumentService $documents;

    public function __construct()
    {
        $this->documents = new DocumentService(new DocumentRepository(new Document()));
    }

    public function upload(Request $request, Response $response, array $args): Response
    {
        $payload = (array) $request->getParsedBody();
        $document = $this->documents->upload($args['id'], $payload);
        return $this->json($response, $document->toArray(), 201);
    }

    public function list(Request $request, Response $response, array $args): Response
    {
        $caseDocuments = Document::where('case_id', $args['id'])->with('versions')->get();
        return $this->json($response, $caseDocuments->toArray());
    }

    public function versions(Request $request, Response $response, array $args): Response
    {
        $document = $this->documents->versions($args['id']);
        return $this->json($response, $document->toArray());
    }

    public function fullTextSearch(Request $request, Response $response): Response
    {
        $term = $request->getQueryParams()['q'] ?? '';
        $result = $this->documents->search($term);
        return $this->json($response, $result->toArray());
    }
}
