<?php

namespace App\Controllers;

use App\Models\FinanceTransaction;
use App\Repositories\FinanceRepository;
use App\Services\Finance\FinanceService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FinanceController extends Controller
{
    private FinanceService $financeService;

    public function __construct()
    {
        $this->financeService = new FinanceService(new FinanceRepository(new FinanceTransaction()));
    }

    public function cashFlow(Request $request, Response $response): Response
    {
        $summary = $this->financeService->cashFlowSummary();
        return $this->json($response, $summary);
    }

    public function storeTransaction(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $transaction = $this->financeService->store($payload);
        return $this->json($response, $transaction->toArray(), 201);
    }

    public function monthlyReport(Request $request, Response $response): Response
    {
        $summary = $this->financeService->cashFlowSummary();
        return $this->json($response, $summary);
    }

    public function cashStats(Request $request, Response $response): Response
    {
        $stats = $this->financeService->cashStats();
        return $this->json($response, $stats);
    }

    public function cashTransactions(Request $request, Response $response): Response
    {
        $transactions = $this->financeService->cashTransactions();
        return $this->json($response, $transactions);
    }
}
