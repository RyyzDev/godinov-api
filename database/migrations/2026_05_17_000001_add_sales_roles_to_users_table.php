<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ceo', 'coo', 'cto', 'cfo', 'cdo', 'pm', 'uiux', 'backend', 'frontend', 'admin', 'finance', 'designer', 'marketing', 'super_sales', 'sales'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ceo', 'coo', 'cto', 'cfo', 'cdo', 'pm', 'uiux', 'backend', 'frontend', 'admin', 'finance', 'designer', 'marketing'])->change();
        });
    }
};
