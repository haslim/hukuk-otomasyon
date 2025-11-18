<?php

namespace App\Services\Finance;

use App\Repositories\FinanceRepository;
use Carbon\Carbon;

class FinanceService
{
    public function __construct(private readonly FinanceRepository $finance)
    {
    }

    public function cashFlowSummary(): array
    {
        $now = Carbon::now();
        $summary = $this->finance->monthlySummary($now->year, $now->month);
        return [
            'income' => $summary->firstWhere('type', 'income')->total ?? 0,
            'expense' => $summary->firstWhere('type', 'expense')->total ?? 0
        ];
    }

    public function store(array $data)
    {
        return $this->finance->create($data);
    }

    public function cashStats(): array
    {
        return $this->finance->getCashStats();
    }

    public function cashTransactions(?string $caseId = null): array
    {
        return $this->finance->getCashTransactions($caseId)->toArray();
    }
}
