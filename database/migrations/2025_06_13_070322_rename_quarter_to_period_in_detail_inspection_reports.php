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
        Schema::table("detail_inspection_reports", function (Blueprint $table) {
            $table->renameColumn("quarter", "period");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("detail_inspection_reports", function (Blueprint $table) {
            $table->renameColumn("period", "quarter");
        });
    }
};
