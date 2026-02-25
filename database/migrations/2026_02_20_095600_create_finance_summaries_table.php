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
        Schema::create('finance_summaries', function (Blueprint $table) {
            $table->id();
            // total_revenue: Akumulasi dari invoice 'Paid'
            $table->decimal('total_revenue', 15, 2)->default(0)->comment("Akumulasi dari invoice 'Paid'");
            
            // total_receivables: Akumulasi dari invoice 'Unpaid' & 'Overdue'
            $table->decimal('total_receivables', 15, 2)->default(0)->comment("Akumulasi dari invoice 'Unpaid' & 'Overdue'");

            // total_partially_paid: Akumulasi dari invoice 'Partially'.
            // Berguna untuk melacak pembayaran yang belum lunas sepenuhnya.
            $table->decimal('total_partially_paid_revenue', 15, 2)->default(0)->comment("Akumulasi pendapatan dari invoice 'Partially'");

            // total_expenses: Akumulasi dari semua expense yang 'Approved' atau 'Paid'
            $table->decimal('total_expenses', 15, 2)->default(0)->comment("Akumulasi dari semua expense yang disetujui");
            
            // net_cash_flow: Kas bersih di tangan (Revenue - Expenses)
            $table->decimal('net_cash_flow', 15, 2)->default(0)->comment("Kas bersih di tangan (Revenue - Expenses)");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_summaries');
    }
};
