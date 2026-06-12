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
            $table->string('cancel_reason')->nullable()->after('is_cancel');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('monthly_budget_summary_reports', 'cancel_reason')) {
            Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
                $table->dropColumn('cancel_reason');
            });
        }
    }
};
