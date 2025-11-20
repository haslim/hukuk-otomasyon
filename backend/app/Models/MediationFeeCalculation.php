<?php

namespace App\Models;

class MediationFeeCalculation extends BaseModel
{
    protected $table = 'mediation_fee_calculations';

    protected $fillable = [
        'case_id',
        'client_id',
        'calculation_type',
        'party_count',
        'subject_value',
        'base_fee',
        'vat_rate',
        'vat_amount',
        'total_fee',
        'fee_per_party',
        'calculation_details',
        'calculation_date',
        'created_by'
    ];

    protected $casts = [
        'subject_value' => 'float',
        'base_fee' => 'float',
        'vat_rate' => 'float',
        'vat_amount' => 'float',
        'total_fee' => 'float',
        'fee_per_party' => 'float',
        'calculation_details' => 'array',
        'calculation_date' => 'date'
    ];

    public function case()
    {
        return $this->belongsTo(CaseModel::class, 'case_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'calculation_id');
    }

    public function getFormattedBaseFeeAttribute()
    {
        return number_format($this->base_fee, 2, ',', '.') . ' ₺';
    }

    public function getFormattedVatAmountAttribute()
    {
        return number_format($this->vat_amount, 2, ',', '.') . ' ₺';
    }

    public function getFormattedTotalFeeAttribute()
    {
        return number_format($this->total_fee, 2, ',', '.') . ' ₺';
    }

    public function getFormattedFeePerPartyAttribute()
    {
        return number_format($this->fee_per_party, 2, ',', '.') . ' ₺';
    }

    public function getFormattedSubjectValueAttribute()
    {
        return number_format($this->subject_value, 2, ',', '.') . ' ₺';
    }
}
