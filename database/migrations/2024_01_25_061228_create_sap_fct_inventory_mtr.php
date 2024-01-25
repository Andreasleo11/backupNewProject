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
        Schema::create('sap_fct_inventory_mtr', function (Blueprint $table) {
            $table->id();
            $table->string('fg_code', 255);
            $table->string('material_code', 255)->nullable();
            $table->string('material_name', 255)->nullable();
            $table->decimal('material_quantity', 12,5)->nullable();
            $table->decimal('in_stock', 12,5)->nullable();
            $table->integer('item_group')->nullable();
            $table->string('vendor_code', 255)->nullable();
            $table->string('vendor_name', 255)->nullable();
            $table->string('Measure', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_fct_inventory_mtr');
    }
};
