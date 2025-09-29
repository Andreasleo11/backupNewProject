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
        Schema::create('barcode_packaging_master', function (Blueprint $table) {
            $table->id();
            $table->string('noDokumen')->unique()->nullable();
            $table->datetime('dateScan');
            $table->string('tipeBarcode');
            $table->string('location');
            $table->boolean('isFinish')->default(0);
            $table->string('finishDokumen')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_packaging_master');
    }
};
