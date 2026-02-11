<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up()
    {
        Schema::table('rab_project_settings', function (Blueprint $table) {
            $table->integer('bep_target_unit')->default(100)->after('variable_cost_percentage');
        });
    }

    public function down()
    {
        Schema::table('rab_project_settings', function (Blueprint $table) {
            $table->dropColumn('bep_target_unit');
        });
    }
};
