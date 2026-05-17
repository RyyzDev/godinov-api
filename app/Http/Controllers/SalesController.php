<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SalesController extends Controller
{
    /**
     * Lihat daftar sales users
     * Super Sales dapat lihat sales users mereka, Admin dapat lihat semua
     */
    public function index()
    {
        try {
            $user = auth()->user();

            // Admin dapat lihat semua sales
            if ($user->role === 'admin') {
                $sales = User::whereIn('role', ['sales', 'super_sales'])
                    ->select('id', 'name', 'email', 'role', 'alias', 'is_active')
                    ->get();
            } else {
                // Super sales hanya bisa lihat sales users saja
                $sales = User::where('role', 'sales')
                    ->select('id', 'name', 'email', 'role', 'alias', 'is_active')
                    ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $sales
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil daftar sales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buat sales user baru (hanya super_sales & admin)
     */
    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'alias' => 'nullable|string|max:50|unique:users,alias',
                'bio' => 'nullable|string|max:500',
                'is_active' => 'nullable|boolean'
            ]);

            // Set role ke 'sales'
            $validated['role'] = 'sales';
            $validated['password'] = Hash::make($validated['password']);
            $validated['is_active'] = $validated['is_active'] ?? true;

            // Buat user baru
            $sales = User::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Sales user berhasil dibuat',
                'data' => $sales->only(['id', 'name', 'email', 'role', 'alias'])
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat sales user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lihat detail sales user
     */
    public function show($id)
    {
        try {
            $sales = User::where('role', 'sales')
                ->where('id', $id)
                ->select('id', 'name', 'email', 'role', 'alias', 'bio', 'is_active', 'created_at')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $sales
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sales user tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil detail sales user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sales user (hanya super_sales & admin)
     */
    public function update(Request $request, $id)
    {
        try {
            $sales = User::where('role', 'sales')
                ->where('id', $id)
                ->firstOrFail();

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($id)],
                'alias' => ['sometimes', 'string', 'max:50', Rule::unique('users', 'alias')->ignore($id)],
                'bio' => 'nullable|string|max:500',
                'is_active' => 'sometimes|boolean'
            ]);

            // Hash password jika di-update
            if ($request->has('password')) {
                $request->validate(['password' => 'required|string|min:8']);
                $validated['password'] = Hash::make($request->password);
            }

            $sales->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Sales user berhasil diupdate',
                'data' => $sales->only(['id', 'name', 'email', 'role', 'alias'])
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sales user tidak ditemukan'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate sales user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus sales user (hanya admin)
     */
    public function destroy($id)
    {
        try {
            // Hanya admin yang bisa delete
            if (auth()->user()->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya admin yang dapat menghapus sales user'
                ], 403);
            }

            $sales = User::where('role', 'sales')
                ->where('id', $id)
                ->firstOrFail();

            $sales->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sales user berhasil dihapus'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sales user tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus sales user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status sales user
     */
    public function toggleStatus($id)
    {
        try {
            $sales = User::where('role', 'sales')
                ->where('id', $id)
                ->firstOrFail();

            $sales->is_active = !$sales->is_active;
            $sales->save();

            return response()->json([
                'success' => true,
                'message' => 'Status sales user berhasil diubah',
                'data' => [
                    'id' => $sales->id,
                    'name' => $sales->name,
                    'is_active' => $sales->is_active
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sales user tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status sales user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
