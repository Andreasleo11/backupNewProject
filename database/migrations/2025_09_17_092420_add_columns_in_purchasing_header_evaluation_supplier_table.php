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
        Schema::table("purchasing_header_evaluation_supplier", function (Blueprint $table) {
            $table->string("start_month")->nullable()->after("vendor_name");
            $table->string("end_month")->nullable()->after("start_month");
            $table->integer("year_end")->nullable()->after("year");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("purchasing_header_evaluation_supplier", function (Blueprint $table) {
            $table->dropColumn("start_month");
            $table->dropColumn("end_month");
            $table->dropColumn("year_end");
        });
    }
};
