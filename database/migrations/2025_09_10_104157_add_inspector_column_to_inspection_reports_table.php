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
        Schema::table("inspection_reports", function (Blueprint $table) {
            $table->string("inspector")->after("operator")->default("-");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("inspection_reports", function (Blueprint $table) {
            $table->dropColumn("inspector");
        });
    }
};
