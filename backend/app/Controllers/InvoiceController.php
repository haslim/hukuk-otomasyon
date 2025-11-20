<?php

namespace App\Controllers;

use App\Services\InvoiceService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class InvoiceController extends Controller
{
    private InvoiceService $invoiceService;

    public function __construct()
    {
        $this->invoiceService = new InvoiceService();
    }

    public function index(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $filters = [];

        if (isset($params['client_id'])) {
            $filters['client_id'] = $params['client_id'];
        }

        if (isset($params['case_id'])) {
            $filters['case_id'] = $params['case_id'];
        }

        if (isset($params['status'])) {
            $filters['status'] = $params['status'];
        }

        if (isset($params['date_from'])) {
            $filters['date_from'] = $params['date_from'];
        }

        if (isset($params['date_to'])) {
            $filters['date_to'] = $params['date_to'];
        }

        if (isset($params['search'])) {
            $filters['search'] = $params['search'];
        }

        $invoices = $this->invoiceService->getInvoices($filters);
        return $this->json($response, $invoices);
    }

    public function store(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();
        $data['created_by'] = $request->getAttribute('user_id');

        try {
            $invoice = $this->invoiceService->createInvoice($data);
            return $this->json($response, $invoice->toArray(), 201);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        
        $invoice = $this->invoiceService->getInvoiceById($id);
        
        if (!$invoice) {
            return $this->json($response, ['error' => 'Fatura bulunamadı'], 404);
        }

        return $this->json($response, $invoice->load(['items', 'payments', 'client', 'case', 'calculation'])->toArray());
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = (array) $request->getParsedBody();
        $data['updated_by'] = $request->getAttribute('user_id');

        try {
            $invoice = $this->invoiceService->updateInvoice($id, $data);
            return $this->json($response, $invoice->load(['items', 'payments', 'client', 'case', 'calculation'])->toArray());
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        
        try {
            $success = $this->invoiceService->deleteInvoice($id);
            
            if (!$success) {
                return $this->json($response, ['error' => 'Fatura bulunamadı'], 404);
            }

            return $this->json($response, ['message' => 'Fatura başarıyla silindi']);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function addPayment(Request $request, Response $response, array $args): Response
    {
        $invoiceId = $args['id'];
        $data = (array) $request->getParsedBody();
        $data['created_by'] = $request->getAttribute('user_id');

        try {
            $payment = $this->invoiceService->addPayment($invoiceId, $data);
            return $this->json($response, $payment->toArray(), 201);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function updateStatus(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = (array) $request->getParsedBody();

        if (!isset($data['status'])) {
            return $this->json($response, ['error' => 'Durum alanı gereklidir'], 422);
        }

        try {
            $invoice = $this->invoiceService->updateStatus($id, $data['status']);
            return $this->json($response, $invoice->toArray());
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function getStats(Request $request, Response $response): Response
    {
        try {
            $stats = $this->invoiceService->getInvoiceStats();
            return $this->json($response, $stats);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function generatePdf(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        
        $invoice = $this->invoiceService->getInvoiceById($id);
        
        if (!$invoice) {
            return $this->json($response, ['error' => 'Fatura bulunamadı'], 404);
        }

        try {
            // PDF generation servisi çağrılacak
            // Şimdilik placeholder
            return $this->json($response, [
                'message' => 'PDF generation not implemented yet',
                'invoice_id' => $id,
                'invoice_number' => $invoice->invoice_number
            ]);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }

    public function sendInvoice(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = (array) $request->getParsedBody();

        try {
            $invoice = $this->invoiceService->updateStatus($id, 'sent');
            
            // Email sending servisi çağrılacak
            // Şimdilik placeholder
            
            return $this->json($response, [
                'message' => 'Fatura başarıyla gönderildi',
                'invoice' => $invoice->toArray()
            ]);
        } catch (\Exception $e) {
            return $this->json($response, ['error' => $e->getMessage()], 400);
        }
    }
}
