<?php

namespace App\Models;

class InvoiceItem extends BaseModel
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'item_type',
        'description',
        'quantity',
        'unit_price',
        'line_total',
        'vat_rate',
        'vat_amount',
        'reference_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'float',
        'line_total' => 'float',
        'vat_rate' => 'float',
        'vat_amount' => 'float'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 2, ',', '.') . ' ₺';
    }

    public function getFormattedLineTotalAttribute()
    {
        return number_format($this->line_total, 2, ',', '.') . ' ₺';
    }

    public function getFormattedVatAmountAttribute()
    {
        return number_format($this->vat_amount, 2, ',', '.') . ' ₺';
    }

    public function getItemTypeLabelAttribute()
    {
        $labels = [
            'fee' => 'Hizmet Bedeli',
            'expense' => 'Masraf',
            'tax' => 'Vergi',
            'other' => 'Diğer'
        ];

        return $labels[$this->item_type] ?? $this->item_type;
    }
}
