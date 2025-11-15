<?php

namespace App\Repositories;

use App\Models\FinanceTransaction;
use Carbon\Carbon;
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

    public function getCashStats(): array
    {
        $now = Carbon::now();
        
        $totalIncome = $this->model->newQuery()
            ->where('type', 'income')
            ->sum('amount');
            
        $totalExpense = $this->model->newQuery()
            ->where('type', 'expense')
            ->sum('amount');
            
        $currentMonthIncome = $this->model->newQuery()
            ->where('type', 'income')
            ->whereYear('occurred_on', $now->year)
            ->whereMonth('occurred_on', $now->month)
            ->sum('amount');
            
        $currentMonthExpense = $this->model->newQuery()
            ->where('type', 'expense')
            ->whereYear('occurred_on', $now->year)
            ->whereMonth('occurred_on', $now->month)
            ->sum('amount');

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'net_balance' => $totalIncome - $totalExpense,
            'current_month_income' => $currentMonthIncome,
            'current_month_expense' => $currentMonthExpense,
            'current_month_net' => $currentMonthIncome - $currentMonthExpense,
        ];
    }

    public function getCashTransactions(int $limit = 50): Collection
    {
        return $this->model->newQuery()
            ->orderBy('occurred_on', 'desc')
            ->limit($limit)
            ->get();
    }
}
