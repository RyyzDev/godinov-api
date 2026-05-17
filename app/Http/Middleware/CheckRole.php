<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // Cek apakah user login
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        // Support multiple roles dengan comma-separated string atau array
        $allowedRoles = [];
        foreach ($roles as $roleParam) {
            // Split by comma jika ada multiple roles dalam satu parameter
            $allowedRoles = array_merge($allowedRoles, explode(',', $roleParam));
        }

        // Trim whitespace dari setiap role
        $allowedRoles = array_map('trim', $allowedRoles);

        // Cek apakah user role ada di dalam allowed roles
        if (!in_array($request->user()->role, $allowedRoles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Role Anda tidak memiliki izin.'
            ], 403);
        }

        return $next($request);
    }
}
