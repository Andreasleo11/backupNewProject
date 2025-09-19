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
        Schema::table("monthly_budget_report_summary_details", function (Blueprint $table) {
            $table->integer("last_recorded_stock")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("monthly_budget_report_summary_details", function (Blueprint $table) {
            $table->dropColumn("last_recorded_stock");
        });
    }
};
