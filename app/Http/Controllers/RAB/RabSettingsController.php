<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RabSettingsController extends Controller
{
    public function index()
    {
        // Ambil baris pertama. Jika kosong, buat baru (Singleton pattern sederhana)
        $settings = DB::table('rab_general_settings')->first();

        if (!$settings) {
            $id = DB::table('rab_general_settings')->insertGetId([
                'created_at' => now(), 
                'updated_at' => now()
            ]);
            $settings = DB::table('rab_general_settings')->where('id', $id)->first();
        }

        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    // POST: Update pengaturan global
    public function update(Request $request)
    {
        // Validasi (sama seperti sebelumnya)
        $validated = $request->validate([
            'company_name' => 'nullable|string',
            'company_address' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'prepared_by' => 'nullable|string',
            'default_tax_rate' => 'required|numeric',
            'default_inflation_rate' => 'required|numeric',
            'default_discount_rate' => 'required|numeric',
            'default_hourly_rate' => 'required|numeric',
            'default_variable_cost_percentage' => 'required|numeric',
            'work_hours_per_day' => 'required|integer',
            'work_days_per_month' => 'required|integer',
        ]);

        // Selalu update ID = 1 (atau baris pertama)
        // Kita pakai updateOrInsert dengan kondisi id=1
        DB::table('rab_general_settings')->updateOrInsert(
            ['id' => 1], 
            array_merge($validated, ['updated_at' => now()])
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan Global berhasil diperbarui.'
        ]);
    }

    /**
     * Mengambil setting RAB spesifik untuk satu proyek.
     */
    public function show($projectId)
    {
        try {
            $settings = DB::table('rab_project_settings')
                ->where('project_id', $projectId)
                ->first();

            // Jika data belum ada, kita berikan response sukses dengan data kosong {}
            // agar frontend tidak crash dan menggunakan state default-nya.
            return response()->json([
                'success' => true,
                'data' => $settings ?: new \stdClass()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil pengaturan proyek',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //Update pengaturan project
    public function UpdateSettingByProject(Request $request, $projectId)
    {
        // Validasi opsional (semua field nullable agar bisa update parsial)
        $validated = $request->validate([
            // Profil Proyek (Snapshot)
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'prepared_by' => 'nullable|string|max:100',

            // Financial
            'tax_rate' => 'nullable|numeric',
            'inflation_rate' => 'nullable|numeric',
            'discount_rate' => 'nullable|numeric',
            'variable_cost_percentage' => 'nullable|numeric',
            'bep_target_unit' => 'nullable|integer',
            'currency_code' => 'nullable|string|max:10',

            // Manpower
            'default_hourly_rate' => 'nullable|numeric',
            'work_hours_per_day' => 'nullable|integer',
            'work_days_per_month' => 'nullable|integer',
        ]);

        try {
            // Gunakan updateOrInsert agar logic lebih tangguh
            DB::table('rab_project_settings')->updateOrInsert(
                ['project_id' => $projectId],
                array_merge($validated, ['updated_at' => now()])
            );

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan proyek berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengaturan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}