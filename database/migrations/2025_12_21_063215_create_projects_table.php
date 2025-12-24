<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_code', 50)->unique()->comment('Unique code for client tracking');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('client_name')->nullable()->comment('Client/company name');
            $table->string('service_type')->nullable()->comment('Service type');
            $table->date('deadline')->nullable();
            $table->enum('status', ['Planning', 'In Progress', 'Review', 'Completed', 'On Hold'])->default('Planning');
            $table->integer('team_count')->default(0);
            $table->timestamps();
            
            $table->index('project_code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};