<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('role', ['uiux', 'backend', 'frontend', 'mobile', 'devops']);
            $table->enum('status', ['Todo', 'In Progress', 'Done', 'Blocked'])->default('Todo');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->string('assignee')->nullable();
            $table->text('note')->nullable();
            $table->integer('order')->default(0)->comment('Task order in project');
            $table->integer('duration_seconds')->nullable();
            $table->timestamps();
            $table->timestamp('completed_at')->nullable();
            $table->string('activity_log')->nullable();
            $table->index(['project_id', 'status']);
            $table->index('assignee');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
