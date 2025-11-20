<?php

namespace App\Controllers;

use App\Services\MediationFeeService;
use App\Repositories\BaseRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MediationFeeController extends Controller
{
    private MediationFeeService $feeService;

    public function __construct()
    {
        $this->feeService = new MediationFeeService(new BaseRepository(new \App\Models\MediationFeeCalculation()));
    }

    public function calculate(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        
        // Validasyon
        $errors = $this->feeService->validateCalculationData($data);
        if (!empty($errors)) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        try {
            $result = $this->feeService->calculateFee($data);
            return $this->json($response, $result);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        
        // Validasyon
        $errors = $this->feeService->validateCalculationData($data);
        if (!empty($errors)) {
            return $this->json($response, ['errors' => $errors], 422);
        }

        try {
            $data['created_by'] = $request->getAttribute('user_id');
            $calculation = $this->feeService->storeCalculation($data);
            return $this->json($response, $calculation->toArray(), 201);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $filters = [];

        if (isset($params['case_id'])) {
            $filters['case_id'] = $params['case_id'];
        }

        if (isset($params['client_id'])) {
            $filters['client_id'] = $params['client_id'];
        }

        if (isset($params['calculation_type'])) {
            $filters['calculation_type'] = $params['calculation_type'];
        }

        if (isset($params['date_from'])) {
            $filters['date_from'] = $params['date_from'];
        }

        if (isset($params['date_to'])) {
            $filters['date_to'] = $params['date_to'];
        }

        $calculations = $this->feeService->getCalculations($filters);
        return $this->json($response, $calculations);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        
        $calculation = $this->feeService->getCalculationById($id);
        
        if (!$calculation) {
            return $this->json($response, ['error' => 'Hesaplama bulunamadı'], 404);
        }

        return $this->json($response, $calculation->toArray());
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        
        try {
            $success = $this->feeService->deleteCalculation($id);
            
            if (!$success) {
                return $this->json($response, ['error' => 'Hesaplama bulunamadı'], 404);
            }

            return $this->json($response, ['message' => 'Hesaplama başarıyla silindi']);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function tariffs(Request $request, Response $response): Response
    {
        $tariffs = $this->feeService->getTariffSummary();
        return $this->json($response, $tariffs);
    }

    public function createInvoice(Request $request, Response $response, array $args): Response
    {
        $calculationId = $args['id'];
        $data = (array) $request->getParsedBody();
        $data['created_by'] = $request->getAttribute('user_id');

        try {
            $invoiceService = new \App\Services\InvoiceService();
            $invoice = $invoiceService->createFromCalculation($calculationId, $data);
            return $this->json($response, $invoice->toArray(), 201);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }
}
