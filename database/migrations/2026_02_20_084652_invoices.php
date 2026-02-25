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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel projects yang sudah Anda buat sebelumnya
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            
            // Data Kontak Klien 
            $table->string('client_email');
            $table->string('client_phone')->nullable();
            
            // Tanggal & Termin
            $table->date('invoice_date');
            $table->date('due_date')->comment('Jatuh tempo pembayaran');
            
            // Item Invoice (Disimpan sebagai JSON karena invoice_items adalah array)
            // Di Laravel 10+, ini akan otomatis di-cast menjadi array di Model
            $table->json('invoice_items')->comment('Daftar item: deskripsi, rate, unit, discount');
            
            // Total Nilai
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total setelah diskon & pajak');
            
            // Status Pembayaran Tambahan
            $table->enum('payment_status', ['Unpaid', 'Partially', 'Paid', 'Overdue'])->default('Unpaid');
            
            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('invoice_date');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};