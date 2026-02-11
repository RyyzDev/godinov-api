<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabOpexController extends Controller
{
    public function index($projectId)
    {
        $items = DB::table('rab_opex_items')
            ->where('project_id', $projectId)
            ->get();

        // Kalkulasi Total Tahunan (x12) untuk Summary Card Frontend
        $summaryYearly = [
            'y1' => $items->sum('monthly_cost_y1') * 12,
            'y2' => $items->sum('monthly_cost_y2') * 12,
            'y3' => $items->sum('monthly_cost_y3') * 12,
        ];

        return response()->json([
            'success' => true,
            'summary' => $summaryYearly,
            'data' => $items
        ]);
    }

    public function store(Request $request, $projectId)
    {
        $validated = $request->validate([
            'component_name' => 'required|string',
            'source_reference' => 'nullable|string',
            'monthly_cost_y1' => 'required|numeric',
            'monthly_cost_y2' => 'nullable|numeric', // Jika null, nanti ambil dari y1
            'monthly_cost_y3' => 'nullable|numeric',
        ]);

        // Auto-fill logic jika user kosongkan Y2/Y3
        $y1 = $validated['monthly_cost_y1'];
        $y2 = $validated['monthly_cost_y2'] ?? $y1;
        $y3 = $validated['monthly_cost_y3'] ?? $y2;

        DB::table('rab_opex_items')->insert([
            'project_id' => $projectId,
            'component_name' => $validated['component_name'],
            'source_reference' => $validated['source_reference'],
            'monthly_cost_y1' => $y1,
            'monthly_cost_y2' => $y2,
            'monthly_cost_y3' => $y3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'OPEX Item Saved']);
    }

    public function destroy($id)
    {
        DB::table('rab_opex_items')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}