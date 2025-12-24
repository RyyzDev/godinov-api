<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
     Schema::create('project_progress', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained()->onDelete('cascade');
        $table->string('role_type');
        $table->integer('progress_percentage')->default(0);
        
        // Constraint agar tidak duplikat
        $table->unique(['project_id', 'role_type']);
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_progress');
    }
};
