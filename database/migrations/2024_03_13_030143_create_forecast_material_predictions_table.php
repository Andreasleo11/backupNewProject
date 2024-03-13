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
            $table->string("material_code")->nullable();
            $table->string("material_name")->nullable();
            $table->string("customer")->nullable();
            $table->string("item_no")->nullable();
            $table->string("unit_of_measure")->nullable();
            $table->double("quantity_material",12,5)->nullable();
            $table->string("vendor_code")->nullable();
            $table->text("quantity_forecast")->nullable();
            $table->string("vendor_name")->nullable();
            $table->text("months")->nullable();
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
