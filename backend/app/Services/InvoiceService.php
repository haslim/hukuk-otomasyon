<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\MediationFeeCalculation;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class InvoiceService
{
    private BaseRepository $invoiceRepository;
    private BaseRepository $itemRepository;
    private BaseRepository $paymentRepository;

    public function __construct()
    {
        $this->invoiceRepository = new BaseRepository(new Invoice());
        $this->itemRepository = new BaseRepository(new InvoiceItem());
        $this->paymentRepository = new BaseRepository(new InvoicePayment());
    }

    public function createInvoice(array $data): Invoice
    {
        $invoiceNumber = $this->generateInvoiceNumber();
        
        $invoiceData = [
            'invoice_number' => $invoiceNumber,
            'calculation_id' => $data['calculation_id'] ?? null,
            'client_id' => $data['client_id'],
            'case_id' => $data['case_id'] ?? null,
            'issue_date' => $data['issue_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? now()->addDays(30)->toDateString(),
            'status' => $data['status'] ?? 'draft',
            'subtotal' => $data['subtotal'] ?? 0,
            'vat_rate' => $data['vat_rate'] ?? 18,
            'vat_amount' => $data['vat_amount'] ?? 0,
            'total_amount' => $data['total_amount'] ?? 0,
            'notes' => $data['notes'] ?? null,
            'client_details' => $data['client_details'] ?? null,
            'created_by' => $data['created_by'] ?? null
        ];

        $invoice = $this->invoiceRepository->create($invoiceData);

        if (!$invoice) {
            throw new \Exception('Fatura oluşturulamadı');
        }

        // Fatura kalemlerini ekle
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $itemData) {
                $this->addInvoiceItem($invoice->id, $itemData);
            }
        }

        // Hesaplama varsa kalemleri otomatik oluştur
        if (isset($data['calculation_id']) && $data['calculation_id']) {
            $this->createItemsFromCalculation($invoice->id, $data['calculation_id']);
        }

        return $invoice->fresh(['items', 'client', 'case', 'calculation']);
    }

    public function createFromCalculation(string $calculationId, array $additionalData = []): Invoice
    {
        $calculation = MediationFeeCalculation::find($calculationId);
        
        if (!$calculation) {
            throw new \Exception('Hesaplama bulunamadı');
        }

        $invoiceData = [
            'calculation_id' => $calculationId,
            'client_id' => $additionalData['client_id'] ?? $calculation->client_id,
            'case_id' => $additionalData['case_id'] ?? $calculation->case_id,
            'issue_date' => $additionalData['issue_date'] ?? now()->toDateString(),
            'due_date' => $additionalData['due_date'] ?? now()->addDays(30)->toDateString(),
            'status' => $additionalData['status'] ?? 'draft',
            'subtotal' => $calculation->base_fee,
            'vat_rate' => $calculation->vat_rate,
            'vat_amount' => $calculation->vat_amount,
            'total_amount' => $calculation->total_fee,
            'notes' => $additionalData['notes'] ?? null,
            'client_details' => $additionalData['client_details'] ?? null,
            'created_by' => $additionalData['created_by'] ?? null
        ];

        return $this->createInvoice($invoiceData);
    }

    private function createItemsFromCalculation(string $invoiceId, string $calculationId): void
    {
        $calculation = MediationFeeCalculation::find($calculationId);
        
        if (!$calculation) {
            return;
        }

        // Arabuluculuk ücreti kalemi
        $this->addInvoiceItem($invoiceId, [
            'item_type' => 'fee',
            'description' => 'Arabuluculuk Hizmet Bedeli (' . $calculation->party_count . ' taraf)',
            'quantity' => 1,
            'unit_price' => $calculation->base_fee,
            'vat_rate' => $calculation->vat_rate,
            'vat_amount' => $calculation->vat_amount,
            'reference_id' => $calculationId
        ]);
    }

    public function addInvoiceItem(string $invoiceId, array $itemData): InvoiceItem
    {
        $itemData['invoice_id'] = $invoiceId;
        
        if (!isset($itemData['line_total'])) {
            $itemData['line_total'] = ($itemData['unit_price'] ?? 0) * ($itemData['quantity'] ?? 1);
        }

        if (!isset($itemData['vat_amount']) && isset($itemData['vat_rate'])) {
            $itemData['vat_amount'] = $itemData['line_total'] * ($itemData['vat_rate'] / 100);
        }

        $item = $this->itemRepository->create($itemData);
        
        if (!$item) {
            throw new \Exception('Fatura kalemi oluşturulamadı');
        }

        return $item;
    }

    public function updateInvoice(string $id, array $data): Invoice
    {
        $invoice = $this->invoiceRepository->findById($id);
        
        if (!$invoice) {
            throw new \Exception('Fatura bulunamadı');
        }

        if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
            throw new \Exception('Ödenmiş veya iptal edilmiş fatura güncellenemez');
        }

        // Kalemleri güncelle
        if (isset($data['items']) && is_array($data['items'])) {
            // Mevcut kalemleri sil
            $this->itemRepository->deleteWhere(['invoice_id' => $id]);
            
            // Yeni kalemleri ekle
            foreach ($data['items'] as $itemData) {
                $this->addInvoiceItem($id, $itemData);
            }
        }

        unset($data['items']); // items'ı ana veriden çıkar

        $data['updated_by'] = $data['updated_by'] ?? null;

        $updatedInvoice = $this->invoiceRepository->update($id, $data);
        
        if (!$updatedInvoice) {
            throw new \Exception('Fatura güncellenemedi');
        }

        return $updatedInvoice;
    }

    public function getInvoiceById(string $id): ?Invoice
    {
        return $this->invoiceRepository->findById($id);
    }

    public function getInvoices(array $filters = []): array
    {
        $query = $this->invoiceRepository->newQuery();

        if (isset($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (isset($filters['case_id'])) {
            $query->where('case_id', $filters['case_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('issue_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('issue_date', '<=', $filters['date_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        return $query->with(['items', 'payments', 'client', 'case', 'calculation'])
                     ->orderBy('issue_date', 'desc')
                     ->get()
                     ->toArray();
    }

    public function deleteInvoice(string $id): bool
    {
        $invoice = $this->invoiceRepository->findById($id);
        
        if (!$invoice) {
            return false;
        }

        if ($invoice->status === 'paid' || $invoice->status === 'sent') {
            throw new \Exception('Gönderilmiş veya ödenmiş fatura silinemez');
        }

        // İlgili kalemleri ve ödemeleri sil
        $this->itemRepository->deleteWhere(['invoice_id' => $id]);
        $this->paymentRepository->deleteWhere(['invoice_id' => $id]);

        return $this->invoiceRepository->delete($id);
    }

    public function addPayment(string $invoiceId, array $paymentData): InvoicePayment
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        
        if (!$invoice) {
            throw new \Exception('Fatura bulunamadı');
        }

        $paymentData['invoice_id'] = $invoiceId;
        $payment = $this->paymentRepository->create($paymentData);

        if (!$payment) {
            throw new \Exception('Ödeme oluşturulamadı');
        }

        // Fatura durumunu güncelle
        $this->updateInvoiceStatus($invoiceId);

        return $payment;
    }

    private function updateInvoiceStatus(string $invoiceId): void
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        
        if (!$invoice) {
            return;
        }

        $totalPaid = $this->paymentRepository->sumWhere('amount', ['invoice_id' => $invoiceId]);
        
        $status = 'draft';
        if ($totalPaid >= $invoice->total_amount) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'sent';
        }

        $this->invoiceRepository->update($invoiceId, [
            'paid_amount' => $totalPaid,
            'status' => $status
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = 'FAT-' . $year;
        
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
                              ->orderBy('invoice_number', 'desc')
                              ->first();

        if ($lastInvoice) {
            $lastNumber = intval(str_replace($prefix . '-', '', $lastInvoice->invoice_number));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    public function getInvoiceStats(): array
    {
        $query = $this->invoiceRepository->newQuery();
        
        $total = $query->count();
        $draft = $query->where('status', 'draft')->count();
        $sent = $query->where('status', 'sent')->count();
        $paid = $query->where('status', 'paid')->count();
        $overdue = $query->where('status', 'sent')
                         ->where('due_date', '<', now())
                         ->count();

        $totalAmount = $this->invoiceRepository->sumWhere('total_amount', []);
        $paidAmount = $this->paymentRepository->sumWhere('amount', []);
        $unpaidAmount = $totalAmount - $paidAmount;

        return [
            'total_invoices' => $total,
            'draft_invoices' => $draft,
            'sent_invoices' => $sent,
            'paid_invoices' => $paid,
            'overdue_invoices' => $overdue,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'unpaid_amount' => $unpaidAmount
        ];
    }

    public function updateStatus(string $id, string $status): Invoice
    {
        $invoice = $this->invoiceRepository->findById($id);
        
        if (!$invoice) {
            throw new \Exception('Fatura bulunamadı');
        }

        // Durum geçişlerini kontrol et
        $allowedTransitions = [
            'draft' => ['sent', 'cancelled'],
            'sent' => ['paid', 'cancelled'],
            'paid' => [],
            'cancelled' => ['draft'],
            'overdue' => ['paid', 'cancelled']
        ];

        if (!in_array($status, $allowedTransitions[$invoice->status] ?? [])) {
            throw new \Exception('Bu durum geçişi yapılamaz');
        }

        $updatedInvoice = $this->invoiceRepository->update($id, ['status' => $status]);
        
        if (!$updatedInvoice) {
            throw new \Exception('Fatura durumu güncellenemedi');
        }

        return $updatedInvoice;
    }
}
