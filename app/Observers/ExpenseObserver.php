<?php

namespace App\Observers;

use App\Models\Finance\Expense;
use App\Services\Finance\FinanceService;

class ExpenseObserver
{
    protected $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    private function shouldRecalculate(Expense $expense): bool
    {
        // Recalculate if a new expense is added, one is deleted,
        // or if the amount or status changes for an existing one.
        return $expense->isDirty('amount') || $expense->isDirty('status') || !$expense->exists;
    }

    public function saved(Expense $expense): void
    {
        if ($this->shouldRecalculate($expense)) {
            $this->financeService->recalculateSummary();
        }
    }

    public function deleted(Expense $expense): void
    {
        $this->financeService->recalculateSummary();
    }
}
