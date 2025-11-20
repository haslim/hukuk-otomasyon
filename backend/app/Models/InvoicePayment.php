<?php

namespace App\Models;

class InvoicePayment extends BaseModel
{
    protected $table = 'invoice_payments';

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'payment_reference',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'float',
        'payment_date' => 'date'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2, ',', '.') . ' ₺';
    }

    public function getPaymentMethodLabelAttribute()
    {
        $labels = [
            'cash' => 'Nakit',
            'bank_transfer' => 'Havale/EFT',
            'credit_card' => 'Kredi Kartı',
            'check' => 'Çek'
        ];

        return $labels[$this->payment_method] ?? $this->payment_method;
    }
}
