<?php

namespace App\Services\Finance;

use App\Models\Finance\FinanceSummary;
use App\Models\Invoice;
use App\Models\Finance\Expense;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Recalculate and update the financial summary.
     */
    public function recalculateSummary()
    {
        DB::transaction(function () {
            // Get or create the summary record
            $summary = FinanceSummary::firstOrCreate([]);

            // Calculate totals from Invoices
            $paidRevenue = Invoice::where('payment_status', 'Paid')->sum('total_amount');
            $partiallyPaidRevenue = Invoice::where('payment_status', 'Partially')->sum('total_amount');
            $receivables = Invoice::whereIn('payment_status', ['Unpaid', 'Overdue'])->sum('total_amount');

            // Calculate totals from Expenses
            $expenses = Expense::whereIn('status', ['Approved', 'Paid'])->sum('amount');
            
            // Calculate net cash flow
            // Assuming cash on hand is what's fully paid + partially paid - expenses
            $netCashFlow = ($paidRevenue + $partiallyPaidRevenue) - $expenses;

            // Update the summary record
            $summary->update([
                'total_revenue' => $paidRevenue,
                'total_partially_paid_revenue' => $partiallyPaidRevenue,
                'total_receivables' => $receivables,
                'total_expenses' => $expenses,
                'net_cash_flow' => $netCashFlow,
            ]);
        });
    }
}
