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
    Schema::create('rab_general_settings', function (Blueprint $table) {
        $table->id();
        
        // 1. Profil Perusahaan (Global)
        $table->string('company_name')->nullable()->default('My Company');
        $table->text('company_address')->nullable();
        $table->string('contact_email')->nullable();
        $table->string('prepared_by')->nullable()->default('Admin');

        // 2. Default Finansial (Akan dicopy ke project baru)
        $table->decimal('default_tax_rate', 5, 2)->default(11.00);
        $table->decimal('default_inflation_rate', 5, 2)->default(5.00);
        $table->decimal('default_discount_rate', 5, 2)->default(10.00);
        
        // 3. Default Manpower
        $table->decimal('default_hourly_rate', 15, 2)->default(150000);
        $table->decimal('default_variable_cost_percentage', 5, 2)->default(35.00);
        $table->integer('work_hours_per_day')->default(8);
        $table->integer('work_days_per_month')->default(22);
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
