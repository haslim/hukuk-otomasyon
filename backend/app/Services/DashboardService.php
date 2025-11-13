<?php

namespace App\Services;

use App\Models\CaseModel;
use App\Models\FinanceTransaction;
use App\Models\Task;
use Carbon\Carbon;

class DashboardService
{
    public function overview(): array
    {
        $today = Carbon::today();
        return [
            'hearings_today' => CaseModel::whereHas('hearings', fn ($q) => $q->whereDate('hearing_date', $today))->count(),
            'upcoming_deadlines' => Task::whereDate('due_date', '<=', $today->copy()->addDays(7))->count(),
            'open_tasks' => Task::where('status', 'open')->count(),
            'cash_summary' => [
                'income' => FinanceTransaction::where('type', 'income')->sum('amount'),
                'expense' => FinanceTransaction::where('type', 'expense')->sum('amount')
            ]
        ];
    }
}
