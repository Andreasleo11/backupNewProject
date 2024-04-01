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
        Schema::create('sap_inventory_mtr', function (Blueprint $table) {
            $table->string("fg_code");
            $table->string("material_code")->nullable();
            $table->string("material_name")->nullable();
            $table->decimal("bom_quantity", 12,5)->nullable();
            $table->decimal("in_stock", 12,5)->nullable();
            $table->integer("item_group")->nullable();
            $table->string("vendor_code")->nullable();
            $table->string("vendor_name")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_inventory_mtr');
    }
};
