<?php

namespace App\Models;

class MediationFeeTariff extends BaseModel
{
    protected $table = 'mediation_fee_tariffs';

    protected $fillable = [
        'tariff_type',
        'min_value',
        'max_value',
        'fee_amount',
        'fee_percentage',
        'party_count_rule',
        'is_active',
        'valid_from',
        'valid_to',
        'description',
        'created_by'
    ];

    protected $casts = [
        'min_value' => 'float',
        'max_value' => 'float',
        'fee_amount' => 'float',
        'fee_percentage' => 'float',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTariffTypeLabelAttribute()
    {
        $labels = [
            'standard' => 'Standart Arabuluculuk',
            'commercial' => 'Ticari Uyuşmazlıklar',
            'urgent' => 'Acil Arabuluculuk'
        ];

        return $labels[$this->tariff_type] ?? $this->tariff_type;
    }

    public function getPartyCountRuleLabelAttribute()
    {
        $labels = [
            'per_party' => 'Taraf Başına',
            'total' => 'Toplam Ücret'
        ];

        return $labels[$this->party_count_rule] ?? $this->party_count_rule;
    }

    public function getFormattedMinValueAttribute()
    {
        return number_format($this->min_value, 2, ',', '.') . ' ₺';
    }

    public function getFormattedMaxValueAttribute()
    {
        return $this->max_value ? number_format($this->max_value, 2, ',', '.') . ' ₺' : 'Sınırsız';
    }

    public function getFormattedFeeAmountAttribute()
    {
        return $this->fee_amount ? number_format($this->fee_amount, 2, ',', '.') . ' ₺' : '-';
    }

    public function getFormattedFeePercentageAttribute()
    {
        return $this->fee_percentage ? number_format($this->fee_percentage, 2, ',', '.') . '%' : '-';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('valid_to')
                          ->orWhere('valid_to', '>=', now());
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('tariff_type', $type);
    }

    public function scopeForValue($query, $value)
    {
        return $query->where('min_value', '<=', $value)
                    ->where(function ($q) use ($value) {
                        $q->whereNull('max_value')
                          ->orWhere('max_value', '>=', $value);
                    });
    }
}
