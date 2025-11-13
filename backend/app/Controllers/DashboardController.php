<?php

namespace App\Controllers;

use App\Services\DashboardService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DashboardController extends Controller
{
    private DashboardService $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardService();
    }

    public function index(Request $request, Response $response): Response
    {
        $data = $this->dashboard->overview();
        return $this->json($response, $data);
    }
}
