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
        Schema::table("barcode_packaging_master", function (Blueprint $table) {
            $table->dropColumn("isFinish");
            $table->dropColumn("finishDokumen");
            $table->dropColumn("finishDateScan");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("barcode_packaging_master", function (Blueprint $table) {
            $table->boolean("isFinish");
            $table->string("finishDokumen");
            $table->datetime("finishDateScan");
        });
    }
};
