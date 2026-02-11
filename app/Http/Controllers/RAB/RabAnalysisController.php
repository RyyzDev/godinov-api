<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RabAnalysisController extends Controller
{
 public function getSummary($projectId)
{
    try {
        // 1. Ambil Settings (Handle jika null)
        $settings = DB::table('rab_project_settings')
            ->where('project_id', $projectId)
            ->first();
            
        // Gunakan safety check agar tidak error 'property of non-object'
        $discountRate = $settings ? ($settings->discount_rate ?? 10) : 10; 
        
        // 2. Hitung CAPEX (Cash Out T0)
        $totalCapex = DB::table('rab_capex_modules')
            ->where('project_id', $projectId)
            ->sum('total_cost');

        // 3. Hitung OPEX per Tahun (Cash Out T1-T3)
        $opexItems = DB::table('rab_opex_items')->where('project_id', $projectId)->get();
        
        $opexYearly = [
            // Tahun 1: Total monthly_cost_y1 * 12
            1 => $opexItems->sum(fn($i) => ($i->monthly_cost_y1 ?? 0) * 12),
            
            // Tahun 2: Jika monthly_cost_y2 kosong, gunakan y1 (Asumsi Flat)
            2 => $opexItems->sum(fn($i) => ($i->monthly_cost_y2 ?? $i->monthly_cost_y1 ?? 0) * 12),
            
            // Tahun 3: Jika y3 kosong, cek y2, jika kosong cek y1
            3 => $opexItems->sum(fn($i) => ($i->monthly_cost_y3 ?? $i->monthly_cost_y2 ?? $i->monthly_cost_y1 ?? 0) * 12),
        ];

        // 4. Hitung REVENUE per Tahun (Cash In T1-T3)
        $revStreams = DB::table('rab_revenue_streams')->where('project_id', $projectId)->get();
        $revenueYearly = [1 => 0, 2 => 0, 3 => 0];

        foreach($revStreams as $stream) {
            $multiplier = ($stream->frequency === 'monthly') ? 12 : 1;
            $revenueYearly[1] += (($stream->volume_y1 ?? 0) * ($stream->price_y1 ?? 0) * $multiplier);
            $revenueYearly[2] += (($stream->volume_y2 ?? 0) * ($stream->price_y2 ?? 0) * $multiplier);
            $revenueYearly[3] += (($stream->volume_y3 ?? 0) * ($stream->price_y3 ?? 0) * $multiplier);
        }

        // 5. Kalkulasi Arus Kas (Net Cash Flow & Cumulative)
        $cashFlows = [];
        $cumulative = -floatval($totalCapex);
        
        // Push T0 (Investasi Awal)
        $cashFlows[] = [
            'year' => 'T0',
            'label' => 'Investasi Awal',
            'cash_in' => 0,
            'cash_out' => floatval($totalCapex),
            'net_flow' => -floatval($totalCapex),
            'cumulative' => $cumulative
        ];

        // Loop T1 - T3
        $npv = -floatval($totalCapex); // NPV Start minus investment
        $paybackPeriod = null;
        
        for ($i = 1; $i <= 3; $i++) {
            $in = floatval($revenueYearly[$i]);
            $out = floatval($opexYearly[$i]);
            $net = $in - $out;
            
            $prevCumulative = $cumulative;
            $cumulative += $net;

            // Hitung NPV: Net / (1+r)^t
            $npv += $net / pow(1 + ($discountRate/100), $i);

            // Hitung Payback Period (Interpolasi Linear)
            // Cek transisi dari negatif (hutang) ke positif (lunas)
            if ($paybackPeriod === null && $prevCumulative < 0 && $cumulative >= 0) {
                // Rumus: Tahun Sebelum + (Sisa Hutang Absolut / Net Flow Tahun Ini)
                $fraction = abs($prevCumulative) / ($net == 0 ? 1 : $net); 
                $paybackPeriod = ($i - 1) + $fraction;
            }

            $cashFlows[] = [
                'year' => 'T'.$i,
                'label' => 'Tahun '.$i,
                'cash_in' => $in,
                'cash_out' => $out,
                'net_flow' => $net,
                'cumulative' => $cumulative
            ];
        }

        // 6. Final Metrics Calculation
        // ROI = (Total Keuntungan Bersih / Total Modal) * 100
        // $cumulative di akhir loop T3 adalah Total Net Profit 3 Tahun
        $roiPercentage = ($totalCapex > 0) ? ($cumulative / $totalCapex) * 100 : 0;
        
        // Health Score (Simple Logic 0-100)
        $score = 50; 
        if ($roiPercentage > 0) $score += 20; // Untung
        if ($roiPercentage > 25) $score += 10; // Untung Besar
        if ($paybackPeriod !== null && $paybackPeriod < 2.5) $score += 15; // Cepat Balik Modal
        if ($npv > 0) $score += 5; // NPV Positif
        $score = min(100, $score);

        return response()->json([
            'success' => true,
            'metrics' => [
                'total_capex' => floatval($totalCapex),
                'total_revenue_3y' => floatval($revenueYearly[1] + $revenueYearly[2] + $revenueYearly[3]),
                'total_opex_3y' => floatval($opexYearly[1] + $opexYearly[2] + $opexYearly[3]),
                
                // PERBAIKAN DI SINI: Gunakan $cumulative, bukan $runningBalance
                'net_profit_3y' => floatval($cumulative), 
                
                'roi' => round($roiPercentage, 2), // Sesuaikan key dgn frontend (roi)
                'npv_value' => round($npv, 2),
                'payback_period' => $paybackPeriod ? floatval(number_format($paybackPeriod, 1)) : null,
                'viability_score' => $score // Sesuaikan key dgn frontend (viability_score)
            ],
            'j_curve_chart' => $cashFlows,
            'settings' => $settings
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Gagal menghitung analisis: ' . $e->getMessage(),
        ], 500);
    }
}
}