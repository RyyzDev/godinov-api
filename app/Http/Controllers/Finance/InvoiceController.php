<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Http\Requests\Finance\StoreInvoiceRequest;
use App\Http\Requests\Finance\UpdateInvoiceRequest;
use App\Http\Resources\Finance\InvoiceResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Invoice::with('project');

            // Optional filtering
            if ($request->has('status')) {
                $query->where('payment_status', $request->status);
            }

            if ($request->has('project_id')) {
                $query->where('project_id', $request->project_id);
            }

            $invoices = $query->latest()->paginate($request->input('per_page', 15));

            return InvoiceResource::collection($invoices)
                ->additional([
                    'status_code' => 200,
                    'message' => 'Berhasil mengambil daftar invoice.'
                ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Gagal mengambil daftar invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        try {
            $invoice = Invoice::create($request->validated());

            return (new InvoiceResource($invoice))
                ->additional([
                    'status_code' => 201,
                    'message' => 'Invoice berhasil dibuat!'
                ])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Gagal membuat invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($identifier)
    {
        try {
            // Cari berdasarkan ID angka ATAU project_code dari tabel relasi
            $invoice = Invoice::with('project')
                ->where('id', $identifier)
                ->orWhereHas('project', function ($query) use ($identifier) {
                    $query->where('project_code', $identifier);
                })->firstOrFail();

            return new InvoiceResource($invoice);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status_code' => 404,
                'message' => "Invoice dengan identifier {$identifier} tidak ditemukan."
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Gagal mengambil detail invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice): JsonResponse
    {
        try {
            $invoice->update($request->validated());

            return (new InvoiceResource($invoice))
                ->additional([
                    'status_code' => 200,
                    'message' => 'Invoice berhasil diperbarui!'
                ])
                ->response();
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Gagal memperbarui invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice): JsonResponse
    {
        try {
            $invoice->delete();

            return response()->json([
                'status_code' => 200,
                'message' => 'Invoice berhasil dihapus!'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Gagal menghapus invoice.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   /**
     * Memperbarui status pembayaran dan menyimpan bukti transfer.
     */
    public function updateStatus(Request $request, $projectCode): JsonResponse
    {
        // 1. Cari Invoice yang memiliki Project dengan project_code tersebut
        // Menggunakan firstOrFail() agar otomatis melempar error 404 jika tidak ditemukan
        try {
            $invoice = Invoice::whereHas('project', function ($query) use ($projectCode) {
                $query->where('project_code', $projectCode);
            })->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status_code' => 404,
                'message' => "Invoice untuk proyek dengan kode #{$projectCode} tidak ditemukan."
            ], 404);
        }

        // 2. Lanjutkan dengan validasi data dari modal
        $validated = $request->validate([
            'payment_status' => 'required|in:Unpaid,Partially,Paid,Overdue',
            'amount_paid'    => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'payment_proof'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            $updateData = ['payment_status' => $validated['payment_status']];

            // Simpan bukti transfer jika ada
            if ($request->hasFile('payment_proof')) {
                // Hapus file lama jika ada
                if ($invoice->payment_proof_url) {
                    $oldPath = str_replace(asset('storage/'), '', $invoice->payment_proof_url);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }

                $path = $request->file('payment_proof')->store('invoices/payments', 'public');
                $updateData['payment_proof_url'] = asset('storage/' . $path);
            }

            // Update nominal yang dibayar dan catatan
            if ($request->filled('amount_paid')) {
                $updateData['amount_paid'] = $validated['amount_paid'];
                $invoice->notes = ($invoice->notes ?? '') . "\nUpdate Pembayaran: " . $validated['amount_paid'];
            }

            $invoice->update($updateData);

            return response()->json([
                'status_code' => 200,
                'message' => 'Pembayaran berhasil didaftarkan!',
                'data' => new InvoiceResource($invoice->load('project'))
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status_code' => 500,
                'message' => 'Gagal memperbarui status.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
