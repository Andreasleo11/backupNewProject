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
            $table->integer('dept_no');
            $table->date('report_date'); // Stores month and year report
            $table->string('created_autograph')->nullable();
            $table->string('is_known_autograph')->nullable();
            $table->string('approved_autograph')->nullable();
            $table->softDeletes();
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
