<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('project_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_member_id')->constrained()->onDelete('cascade');
            
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('role', ['uiux', 'backend', 'frontend', 'mobile', 'devops', 'pm', 'qa']);
            $table->timestamp('joined_at')->useCurrent();
            
            $table->unique(['project_id', 'team_member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_team');
    }
};
