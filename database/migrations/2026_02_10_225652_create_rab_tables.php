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
        // 1. RAB Project Settings (Konfigurasi spesifik per proyek)
        // Menyimpan asumsi inflasi, pajak, dan variabel cost untuk BEP
        Schema::create('rab_project_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->unique(); // One-to-One dengan Projects
            
             // 1. Profil Perusahaan (Global)
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('prepared_by')->nullable();

             // 2. Finansial
            $table->decimal('tax_rate', 5, 2);
            $table->decimal('inflation_rate', 5, 2);
            $table->decimal('discount_rate', 5, 2);
            $table->decimal('currency_code')->nullable();
            
            // 3. Manpower
            $table->decimal('hourly_rate', 15, 2);
            $table->decimal('variable_cost_percentage', 5, 2);
            $table->integer('work_hours_per_day');
            $table->integer('work_days_per_month');

            $table->timestamps();
        });

        // 2. CAPEX Modules (Biaya Pengembangan Awal)
        // Menyimpan daftar fitur, jam kerja, dan biaya dev
        Schema::create('rab_capex_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            
            $table->string('feature_name'); // Nama Fitur
            $table->string('sub_feature')->nullable(); // Detail Sub-fitur
            $table->enum('complexity', ['Low', 'Medium', 'High'])->default('Low');
            
            $table->integer('estimated_hours')->default(0); // Jam kerja
            $table->decimal('hourly_rate', 15, 2)->default(150000); // Rate per jam saat input
            $table->decimal('total_cost', 15, 2)->default(0); // Hasil hours * rate (disimpan agar historis aman)
            
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        // 3. OPEX Items (Biaya Operasional Rutin)
        // Menyimpan biaya bulanan untuk Tahun 1, 2, dan 3
        Schema::create('rab_opex_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            
            $table->string('component_name'); // e.g. "Sewa VPS"
            $table->string('source_reference')->nullable(); // e.g. "AWS Pricing"
            
            // Biaya Bulanan per Tahun (Untuk mengakomodasi kenaikan harga/inflasi)
            $table->decimal('monthly_cost_y1', 15, 2)->default(0);
            $table->decimal('monthly_cost_y2', 15, 2)->default(0);
            $table->decimal('monthly_cost_y3', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });

        // 4. Revenue Streams (Asumsi Pendapatan Universal)
        // Menyimpan volume dan harga untuk 3 tahun ke depan
        Schema::create('rab_revenue_streams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            
            $table->string('name')->default('Main Revenue'); // e.g. "Langganan Aplikasi"
            $table->string('unit_name')->default('User'); // e.g. "Siswa", "Pcs", "License"
            $table->enum('frequency', ['monthly', 'yearly'])->default('monthly'); // x12 atau x1
            
            // Tahun 1
            $table->integer('volume_y1')->default(0);
            $table->decimal('price_y1', 15, 2)->default(0);
            
            // Tahun 2
            $table->integer('volume_y2')->default(0);
            $table->decimal('price_y2', 15, 2)->default(0);
            
            // Tahun 3
            $table->integer('volume_y3')->default(0);
            $table->decimal('price_y3', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rab_revenue_streams');
        Schema::dropIfExists('rab_opex_items');
        Schema::dropIfExists('rab_capex_modules');
        Schema::dropIfExists('rab_project_settings');
    }
};
