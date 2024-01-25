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
        Schema::create('forecast_material_predictions', function (Blueprint $table) {
            $table->id();
            $table->string('material_code', 255)->nullable();
            $table->string('material_name', 255)->nullable();
            $table->string('customer', 255)->nullable();
            $table->string('item_no', 255)->nullable();
            $table->string('unit_of_measure', 255)->nullable();
            $table->text('quantity_material', 12, 5 )->nullable();
            $table->string('vendor_code', 255)->nullable();
            $table->text('quantity_forecast')->nullable();
            $table->string('vendor_name', 255)->nullable();
            $table->text('months')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('forecast_material_predictions');
    }
};
