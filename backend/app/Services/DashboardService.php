<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\FinanceTransaction;
use Illuminate\Database\QueryException;
use App\Models\Task;
use Carbon\Carbon;

class DashboardService
{
    public function overview(): array
    {
        $today = Carbon::today();

        $income = 0.0;
        $expense = 0.0;

        try {
            $income = FinanceTransaction::where('type', 'income')->sum('amount');
            $expense = FinanceTransaction::where('type', 'expense')->sum('amount');
        } catch (QueryException $e) {
            if (!str_contains($e->getMessage(), 'finance_transactions')) {
                throw $e;
            }
        }

        return [
            'hearings_today' => CaseModel::whereHas('hearings', fn ($q) => $q->whereDate('hearing_date', $today))->count(),
            'upcoming_deadlines' => Task::whereDate('due_date', '<=', $today->copy()->addDays(7))->count(),
            'open_tasks' => Task::where('status', 'open')->count(),
            'cash_summary' => [
                'income' => $income,
                'expense' => $expense,
            ]
        ];
    }
}
