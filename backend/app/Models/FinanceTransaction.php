<?php

namespace App\Models;

class FinanceTransaction extends BaseModel
{
    protected $table = 'finance_transactions';

    protected $casts = [
        'amount' => 'float',
        'occurred_on' => 'datetime'
    ];
}
