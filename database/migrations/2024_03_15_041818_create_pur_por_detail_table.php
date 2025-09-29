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
        Schema::create('pur_por_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('pps_level')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_pair')->nullable();
            $table->date('pps_date')->nullable();
            $table->integer('pps_qty')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('vendor_name')->nullable();
            $table->string('material_code')->nullable();
            $table->string('material_name')->nullable();
            $table->decimal('base_qty', 12, 5)->nullable();
            $table->decimal('material_need', 12, 5)->nullable();
            $table->integer('in_percentage')->nullable();
            $table->decimal('need_plus_percent', 15, 5)->nullable();
            $table->decimal('material_stock', 15, 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pur_por_detail');
    }
};
