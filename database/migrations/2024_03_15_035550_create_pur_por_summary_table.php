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
        Schema::create('pur_por_summary', function (Blueprint $table) {
            $table->id();
            $table->string("vendor_code")->nullable();
            $table->string("vendor_name")->nullable();
            $table->string("material_code")->nullable();
            $table->string("material_name")->nullable();
            $table->decimal("material_total",15,5)->nullable();
            $table->decimal("material_stock",15,5)->nullable();
            $table->decimal("material_fine_total",15,5)->nullable();
            $table->date("minus_date")->nullable();
            $table->decimal("material_forecast",15,5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_por_summary');
    }
};
