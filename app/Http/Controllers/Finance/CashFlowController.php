<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceSummary;
use Illuminate\Http\JsonResponse;
use App\Services\Finance\FinanceService;

class CashFlowController extends Controller
{
    protected $financeService;

    public function __construct(FinanceService $financeService)
    {
        $this->financeService = $financeService;
    }

    /**
     * Display the financial summary.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(): JsonResponse
    {
        try {
            // Get the first record, or create a default one if it doesn't exist
            $summary = FinanceSummary::firstOrCreate([]);

            return response()->json([
                'status_code' => 200,
                'message' => 'Financial summary retrieved successfully.',
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to retrieve financial summary.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually trigger a recalculation of the financial summary.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recalculate(): JsonResponse
    {
        try {
            $this->financeService->recalculateSummary();
            $summary = FinanceSummary::first();

            return response()->json([
                'status_code' => 200,
                'message' => 'Financial summary recalculated successfully.',
                'data' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Failed to recalculate financial summary.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
