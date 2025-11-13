<?php

namespace App\Repositories;

use App\Models\FinanceTransaction;
use Illuminate\Support\Collection;

class FinanceRepository extends BaseRepository
{
    public function __construct(FinanceTransaction $model)
    {
        parent::__construct($model);
    }

    public function monthlySummary(int $year, int $month): Collection
    {
        return $this->model->newQuery()
            ->selectRaw('type, SUM(amount) as total')
            ->whereYear('occurred_on', $year)
            ->whereMonth('occurred_on', $month)
            ->groupBy('type')
            ->get();
    }
}
