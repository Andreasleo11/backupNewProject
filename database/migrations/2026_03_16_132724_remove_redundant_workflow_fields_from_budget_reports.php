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
        Schema::table('monthly_budget_reports', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_budget_reports', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
            if (Schema::hasColumn('monthly_budget_reports', 'workflow_step')) {
                $table->dropColumn('workflow_step');
            }
        });

        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            if (Schema::hasColumn('monthly_budget_summary_reports', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
            if (Schema::hasColumn('monthly_budget_summary_reports', 'workflow_step')) {
                $table->dropColumn('workflow_step');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_budget_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_budget_reports', 'workflow_status')) {
                $table->string('workflow_status')->nullable();
            }
            if (!Schema::hasColumn('monthly_budget_reports', 'workflow_step')) {
                $table->string('workflow_step')->nullable();
            }
        });

        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            if (!Schema::hasColumn('monthly_budget_summary_reports', 'workflow_status')) {
                $table->string('workflow_status')->nullable();
            }
            if (!Schema::hasColumn('monthly_budget_summary_reports', 'workflow_step')) {
                $table->string('workflow_step')->nullable();
            }
        });
    }
};
