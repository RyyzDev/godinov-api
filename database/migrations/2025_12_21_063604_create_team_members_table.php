<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->enum('role', ['uiux', 'backend', 'frontend', 'mobile', 'devops', 'pm', 'qa']);
            $table->string('avatar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('email');
            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_members');
    }
};

