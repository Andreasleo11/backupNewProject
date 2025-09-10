<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("monthly_budget_summary_reports", function (Blueprint $table) {
            $table->id();
            $table->date("report_date"); // Store month and year report choose
            $table->string("created_autograph")->nullable();
            $table->string("is_known_autograph")->nullable();
            $table->string("approved_autograph")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("monthly_budget_summary_reports");
    }
};
