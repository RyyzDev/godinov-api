<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabRevenueController extends Controller
{
    public function index($projectId)
    {
        $streams = DB::table('rab_revenue_streams')
            ->where('project_id', $projectId)
            ->get();
        
        // Helper Calculation untuk Chart Frontend
        $chartData = $streams->map(function($item) {
            $multiplier = ($item->frequency === 'monthly') ? 12 : 1;
            return [
                'name' => $item->name,
                'total_y1' => $item->volume_y1 * $item->price_y1 * $multiplier,
                'total_y2' => $item->volume_y2 * $item->price_y2 * $multiplier,
                'total_y3' => $item->volume_y3 * $item->price_y3 * $multiplier,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $streams,
            'chart_projection' => $chartData
        ]);
    }

    public function store(Request $request, $projectId)
    {
        // Validasi input lengkap 3 tahun
        $validated = $request->validate([
            'name' => 'required|string',
            'unit_name' => 'required|string',
            'frequency' => 'required|in:monthly,yearly',
            'volume_y1' => 'required|numeric',
            'price_y1' => 'required|numeric',
            'volume_y2' => 'required|numeric',
            'price_y2' => 'required|numeric',
            'volume_y3' => 'required|numeric',
            'price_y3' => 'required|numeric',
        ]);

        DB::table('rab_revenue_streams')->insert(array_merge($validated, [
            'project_id' => $projectId,
            'created_at' => now(),
            'updated_at' => now()
        ]));

        return response()->json(['success' => true]);
    }
    
    public function destroy($id)
    {
        DB::table('rab_revenue_streams')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}