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
            $table->bigInteger('creator_id')->after('dept_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_budget_reports', function (Blueprint $table) {
            $table->dropColumn('creator_id');
        });
    }
};
