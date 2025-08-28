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
        Schema::create("barcode_packaging_details", function (Blueprint $table) {
            $table->id();
            $table->integer("masterId");
            $table->string("noDokumen");
            $table->string("partNo");
            $table->string("label");
            $table->datetime("scantime");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("barcode_packaging_details");
    }
};
