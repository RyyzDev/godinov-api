<?php
 
namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
   public function index(Request $request): JsonResponse
{
    $query = Expense::with('user:id,name');

    // Tambahkan Logika Search
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('description', 'like', '%' . $request->search . '%')
              ->orWhereHas('user', function($userQuery) use ($request) {
                  $userQuery->where('name', 'like', '%' . $request->search . '%');
              });
        });
    }

    if ($request->has('status') && $request->status != '') {
        $query->where('status', $request->status);
    }
    
    if ($request->has('category') && $request->category != '') {
        $query->where('category', $request->category);
    }

    $expenses = $query->latest()->paginate($request->input('per_page', 15));
    return response()->json($expenses);
}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|max:100',
            'transaction_date' => 'required|date',
            'status' => 'nullable|in:Pending,Approved,Rejected,Paid',
            'email'            => 'required|email|exists:users,email', //validator user yang mengajukan reimburse
            'receipt_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);

        //validasi user untuk meng get id user berdasarkan email
        $user = \App\Models\User::where('email', $request->email)->first();
        $validated['user_id'] = $user->id;

        if ($request->hasFile('receipt_file')) {
            // Simpan file ke folder storage/app/public/receipts
            $path = $request->file('receipt_file')->store('receipts', 'public');
            // Konversi path internal menjadi URL yang bisa diakses publik
            $validated['receipt_url'] = asset('storage/' . $path); 
        }

        // Hapus field email dari array validated agar tidak error saat create (jika kolom email tidak ada di tabel expenses)
        unset($validated['email']);

        $expense = Expense::create($validated);
        return response()->json($expense, 201);
    }

    public function show(Expense $expense): JsonResponse
    {
        $expense->load('user:id,name');
        return response()->json($expense);
    }

    public function history(Request $request): JsonResponse
    {
        // Mengambil ID user langsung dari token autentikasi (Sanctum/Passport)
        $userId = auth()->id();

        // Query hanya data milik user tersebut
        $query = Expense::with('user:id,name')
            ->where('user_id', $userId);

        // Opsional: Tetap izinkan filter status atau kategori untuk riwayat pribadi
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $history = $query->latest()->paginate(15);

        return response()->json($history);
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        $validated = $request->validate([
            'description' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'category' => 'sometimes|string|max:100',
            'transaction_date' => 'sometimes|date',
            'status' => 'sometimes|in:Pending,Approved,Rejected,Paid',
            'user_id' => 'nullable|exists:users,id',
            'receipt_file' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'notes' => 'nullable|string',
        ]);

        if ($request->hasFile('receipt_file')) {
            // Hapus file lama jika ada
            if ($expense->receipt_url) {
                $oldPath = str_replace(asset('storage/'), '', $expense->receipt_url);
                Storage::disk('public')->delete($oldPath);
            }
            
            $path = $request->file('receipt_file')->store('receipts', 'public');
            $validated['receipt_url'] = asset('storage/' . $path);
        }

        $expense->update($validated);
        return response()->json($expense);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        if ($expense->receipt_url) {
            $oldPath = str_replace(asset('storage/'), '', $expense->receipt_url);
            Storage::disk('public')->delete($oldPath);
        }
        $expense->delete();
        return response()->json(null, 204);
    }
}
