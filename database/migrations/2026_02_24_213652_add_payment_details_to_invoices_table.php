<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Menambahkan kolom untuk menyimpan metode pembayaran (bank_transfer, cash, dll)
            $table->string('payment_method')->nullable()->after('payment_status');
            
            // Menambahkan kolom untuk melacak jumlah yang sudah dibayar (berguna untuk status Partially)
            $table->decimal('amount_paid', 15, 2)->default(0)->after('payment_method');
            
            // Menambahkan kolom untuk menyimpan URL/Path file bukti pembayaran
            $table->string('payment_proof_url')->nullable()->after('amount_paid');
            
            // Menambahkan kolom catatan internal untuk admin (opsional jika belum ada)
            if (!Schema::hasColumn('invoices', 'notes')) {
                $table->text('notes')->nullable()->after('payment_proof_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'amount_paid', 'payment_proof_url']);
        });
    }
};