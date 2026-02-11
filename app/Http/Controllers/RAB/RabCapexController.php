<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabCapexController extends Controller
{
    // Ambil semua modul untuk Project ID tertentu
    public function index($projectId)
    {
        $modules = DB::table('rab_capex_modules')
            ->where('project_id', $projectId)
            ->get();

        // Hitung total summary di backend supaya frontend ringan
        $summary = [
            'total_modules' => $modules->count(),
            'total_hours' => $modules->sum('estimated_hours'),
            'total_capex' => $modules->sum('total_cost'),
            'items' => $modules
        ];

        return response()->json(['success' => true, 'data' => $summary]);
    }

    // Tambah Modul Baru
    public function store(Request $request, $projectId)
    {
        $validated = $request->validate([
            'feature_name' => 'required|string',
            'sub_feature' => 'nullable|string',
            'complexity' => 'required|in:Low,Medium,High',
            'estimated_hours' => 'required|numeric',
            'hourly_rate' => 'required|numeric',
        ]);

        // Auto calculate Total Cost
        $totalCost = $validated['estimated_hours'] * $validated['hourly_rate'];

        $id = DB::table('rab_capex_modules')->insertGetId([
            'project_id' => $projectId,
            'feature_name' => $validated['feature_name'],
            'sub_feature' => $validated['sub_feature'],
            'complexity' => $validated['complexity'],
            'estimated_hours' => $validated['estimated_hours'],
            'hourly_rate' => $validated['hourly_rate'],
            'total_cost' => $totalCost,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Module added', 'id' => $id]);
    }

    public function destroy($id)
    {
        DB::table('rab_capex_modules')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}