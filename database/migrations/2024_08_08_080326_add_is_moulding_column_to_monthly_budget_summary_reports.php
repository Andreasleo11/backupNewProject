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
        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            $table->boolean('is_moulding')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            $table->dropColumn('is_moulding');
        });
    }
};
