<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('date')->nullable()->comment('Can be date or text like "Pending"');
            $table->enum('status', ['completed', 'current', 'pending'])->default('pending');
            $table->text('note')->nullable();
            $table->integer('order')->default(0)->comment('Timeline order');
            $table->timestamps();
            
            $table->index(['project_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_timelines');
    }
};