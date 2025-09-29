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
        Schema::table('purchasing_detail_evaluation_supplier', function (Blueprint $table) {
            $table->integer('year')->nullable()->after('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_detail_evaluation_supplier', function (Blueprint $table) {
            $table->dropColumn('year');
        });
    }
};
