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
        Schema::create("foremind_final", function (Blueprint $table) {
            $table->id();
            $table->string("forecast_code")->nullable();
            $table->string("forecast_name")->nullable();
            $table->string("vendor_code")->nullable();
            $table->string("vendor_name")->nullable();
            $table->date("day_forecast")->nullable();
            $table->string("Item_no")->nullable();
            $table->string("semi_code")->nullable();
            $table->double("quantity_forecast", 12, 5)->nullable();
            $table->integer("item_group")->nullable();
            $table->string("material_code")->nullable();
            $table->string("material_name")->nullable();
            $table->double("quantity_material", 12, 5)->nullable();
            $table->double("Quantity_BomWip", 12, 5)->nullable();
            $table->double("material_prediction", 12, 5)->nullable();
            $table->string("U/M")->nullable();
            $table->date("forecast_date")->nullable();
            $table->integer("quantity");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("foremind_final");
    }
};
