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
        Schema::create('monthly_budget_reports', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('department_id');
            $table->integer('month');
            $table->string('autograph_1');
            $table->string('autograph_2');
            $table->string('autograph_3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_budget_reports');
    }
};
