<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Services\Finance\FinanceService;

class InvoiceObserver
{
    protected $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    private function shouldRecalculate(Invoice $invoice): bool
    {
        // Recalculate if it's a new invoice, it's being deleted,
        // or if total_amount or payment_status has changed.
        return $invoice->isDirty('total_amount') || $invoice->isDirty('payment_status') || !$invoice->exists;
    }

    public function saved(Invoice $invoice): void
    {
        if ($this->shouldRecalculate($invoice)) {
            $this->financeService->recalculateSummary();
        }
    }

    public function deleted(Invoice $invoice): void
    {
        $this->financeService->recalculateSummary();
    }
}
