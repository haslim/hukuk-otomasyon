<?php

namespace App\Repositories;

use App\Models\FinanceTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Ramsey\Uuid\Uuid;

class FinanceRepository extends BaseRepository
{
    public function __construct(FinanceTransaction $model)
    {
        parent::__construct($model);
    }

    public function create(array $data)
    {
        if (!isset($data['id']) || empty($data['id'])) {
            $data['id'] = Uuid::uuid4()->toString();
        }

        return $this->model->newQuery()->create($data);
    }

    public function monthlySummary(int $year, int $month): Collection
    {
        try {
            return $this->model->newQuery()
                ->selectRaw('type, SUM(amount) as total')
                ->whereYear('occurred_on', $year)
                ->whereMonth('occurred_on', $month)
                ->groupBy('type')
                ->get();
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'finance_transactions')) {
                return collect();
            }
            throw $e;
        }
    }

    public function getCashStats(): array
    {
        $now = Carbon::now();

        try {
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
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'finance_transactions')) {
                return [
                    'total_income' => 0.0,
                    'total_expense' => 0.0,
                    'net_balance' => 0.0,
                    'current_month_income' => 0.0,
                    'current_month_expense' => 0.0,
                    'current_month_net' => 0.0,
                ];
            }
            throw $e;
        }
    }

    public function getCashTransactions(int $limit = 50): Collection
    {
        try {
            return $this->model->newQuery()
                ->orderBy('occurred_on', 'desc')
                ->limit($limit)
                ->get();
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'finance_transactions')) {
                return collect();
            }
            throw $e;
        }
    }
}
