<?php

namespace App\Controllers;

use App\Services\SearchService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SearchController extends Controller
{
    private SearchService $searchService;

    public function __construct()
    {
        $this->searchService = new SearchService();
    }

    public function globalSearch(Request $request, Response $response): Response
    {
        $term = $request->getQueryParams()['q'] ?? '';
        $result = $this->searchService->global($term);
        return $this->json($response, $result);
    }
}
