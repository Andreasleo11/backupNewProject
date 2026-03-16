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
            $table->string('workflow_status', 20)->nullable()->index();
            $table->string('workflow_step', 80)->nullable()->index();
        });

        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            $table->string('workflow_status', 20)->nullable()->index();
            $table->string('workflow_step', 80)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_budget_reports', function (Blueprint $table) {
            $table->dropColumn(['workflow_status', 'workflow_step']);
        });

        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            $table->dropColumn(['workflow_status', 'workflow_step']);
        });
    }
};
