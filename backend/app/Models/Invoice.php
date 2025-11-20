<?php

namespace App\Models;

class Invoice extends BaseModel
{
    protected $table = 'invoices';

    protected $fillable = [
        'invoice_number',
        'calculation_id',
        'client_id',
        'case_id',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'paid_amount',
        'notes',
        'client_details',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'float',
        'vat_rate' => 'float',
        'vat_amount' => 'float',
        'total_amount' => 'float',
        'paid_amount' => 'float',
        'client_details' => 'array'
    ];

    public function calculation()
    {
        return $this->belongsTo(MediationFeeCalculation::class, 'calculation_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class, 'invoice_id');
    }

    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getFormattedSubtotalAttribute()
    {
        return number_format($this->subtotal, 2, ',', '.') . ' ₺';
    }

    public function getFormattedVatAmountAttribute()
    {
        return number_format($this->vat_amount, 2, ',', '.') . ' ₺';
    }

    public function getFormattedTotalAmountAttribute()
    {
        return number_format($this->total_amount, 2, ',', '.') . ' ₺';
    }

    public function getFormattedPaidAmountAttribute()
    {
        return number_format($this->paid_amount, 2, ',', '.') . ' ₺';
    }

    public function getFormattedRemainingAmountAttribute()
    {
        return number_format($this->remaining_amount, 2, ',', '.') . ' ₺';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => 'Taslak',
            'sent' => 'Gönderildi',
            'paid' => 'Ödendi',
            'overdue' => 'Gecikmiş',
            'cancelled' => 'İptal Edildi'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'draft' => 'gray',
            'sent' => 'blue',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'orange'
        ];

        return $colors[$this->status] ?? 'gray';
    }

    public function isOverdue()
    {
        return $this->status === 'sent' && $this->due_date < now() && $this->paid_amount < $this->total_amount;
    }

    public function isPaid()
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('paid_amount', '<', 'total_amount')
                    ->whereNotIn('status', ['cancelled', 'paid']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('paid_amount', '<', 'total_amount')
                    ->where('status', 'sent');
    }
}
