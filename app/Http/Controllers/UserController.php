<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Mengambil daftar user untuk kebutuhan delegasi tugas
     */
    public function index()
    {
        try {
            // Mengambil user dengan role tertentu (kecuali admin jika perlu)
            // Anda bisa menyesuaikan filter role sesuai kebutuhan
            $users = User::select('id', 'name', 'role', 'email')
                ->whereIn('role', ['uiux', 'backend', 'frontend', 'mobile', 'devops', 'pm'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}