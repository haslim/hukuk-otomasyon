<?php

namespace App\Models;

class FinanceTransaction extends BaseModel
{
    protected $table = 'cash_transactions';

    protected $fillable = [
        'case_id',
        'type',
        'amount',
        'occurred_on',
        'description'
    ];

    protected $casts = [
        'amount' => 'float',
        'occurred_on' => 'datetime'
    ];
}
