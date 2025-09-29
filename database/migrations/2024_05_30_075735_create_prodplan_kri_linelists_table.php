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
        Schema::create('prodplan_kri_linelists', function (Blueprint $table) {
            $table->id();
            $table->string('area')->nullable();
            $table->string('line_code')->nullable();
            $table->integer('daily_minutes')->nullable();
            $table->string('running_part')->nullable();
            $table->string('material_group')->nullable();
            $table->integer('continue_running')->nullable();
            $table->string('status')->nullable();
            $table->date('start_repair')->nullable();
            $table->date('end_repair')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodplan_kri_linelists');
    }
};
