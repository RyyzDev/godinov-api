<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ItemsInvoiceController extends Controller
{
    /**
     * Mengambil data CAPEX dan OPEX berdasarkan project_id
     * dan memformatnya agar sesuai dengan struktur Invoice Items.
     */
    public function getInvoiceItems($identifier): JsonResponse
    {
        try {
            // Opsional: Jika parameter yang masuk ternyata adalah project_code (string),
            // kita cari dulu ID proyeknya. Jika sudah berupa ID angka, langsung gunakan.
            $projectId = $identifier;
            if (!is_numeric($identifier)) {
                $project = DB::table('projects')->where('project_code', $identifier)->first();
                if (!$project) {
                    return response()->json([
                        'status_code' => 404,
                        'message' => 'Project tidak ditemukan',
                        'data' => []
                    ]);
                }
                $projectId = $project->id;
            }

            // 1. Ambil & Format Data CAPEX (Development)
            // PERBAIKAN: Gunakan 'project_id', bukan 'project_code'
            $capexItems = DB::table('rab_capex_modules')
                ->where('project_id', $projectId) 
                ->get()
                ->map(function ($item) {
                    $sub = $item->sub_feature ? " ({$item->sub_feature})" : "";
                    return [
                        'id' => 'capex_' . $item->id, // ID unik untuk frontend
                        'type' => 'CAPEX',
                        'description' => "{$item->feature_name}{$sub}",
                        'rate' => (float) $item->total_cost,
                        'unit' => 1,
                        'discount' => 0
                    ];
                });

            // 2. Ambil & Format Data OPEX (Operational)
            $opexItems = DB::table('rab_opex_items')
                ->where('project_id', $projectId)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => 'opex_' . $item->id, // ID unik untuk frontend
                        'type' => 'OPEX',
                        'description' => "{$item->component_name} (Biaya 1 Tahun)",
                        'rate' => (float) $item->monthly_cost_y1 * 12, 
                        'unit' => 1,
                        'discount' => 0
                    ];
                });

            // 3. Gabungkan kedua array
            $allItems = $capexItems->merge($opexItems);

            return response()->json([
                'status_code' => 200,
                'message' => 'RAB items retrieved successfully',
                'data' => $allItems
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Gagal mengambil data RAB.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}