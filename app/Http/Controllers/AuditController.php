<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Filter berdasarkan tipe jika ada
        if ($request->type && $request->type !== 'all') {
            $query->where('log_name', $request->type);
        }

        $logs = $query->limit(100)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => strtoupper($log->description),
                'description' => $log->getExtraProperty('reason') ?? $log->description,
                'user_name' => $log->causer->name ?? 'System',
                'user_role' => $log->causer->role ?? 'N/A',
                'ip_address' => $log->getExtraProperty('ip') ?? '0.0.0.0',
                'created_at' => $log->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}