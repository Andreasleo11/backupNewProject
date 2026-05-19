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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('assigned_to_nik')->nullable()->after('assigned_to_user_id');
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->string('target_employee_nik')->nullable()->after('target_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('assigned_to_nik');
        });

        Schema::table('stock_transactions', function (Blueprint $table) {
            $table->dropColumn('target_employee_nik');
        });
    }
};
