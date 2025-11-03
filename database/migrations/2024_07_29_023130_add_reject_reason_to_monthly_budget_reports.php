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
            $table->string('reject_reason')->nullable()->after('approved_autograph');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_budget_reports', function (Blueprint $table) {
            $table->dropColumn('reject_reason');
        });
    }
};
