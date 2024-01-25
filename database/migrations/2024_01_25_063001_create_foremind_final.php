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
        Schema::create('foremind_final', function (Blueprint $table) {
            $table->id();
            $table->string('forecast_code', 255)->nullable();
            $table->string('forecast_name', 255)->nullable();
            $table->string('vendor_code', 255)->nullable();
            $table->string('vendor_name', 255)->nullable();
            $table->date('day_forecast')->nullable();
            $table->string('Item_no', 255)->nullable();
            $table->string('semi_code', 255)->nullable();
            $table->double('quantity_forecast', 12,  5)->nullable();
            $table->integer('item_group')->nullable();
            $table->string('material_code', 255)->nullable();
            $table->string('material_name', 255)->nullable();
            $table->double('quantity_material',  12,  5)->nullable();
            $table->double('Quantity_bomWip',  12,  5)->nullable();
            $table->double('material_prediction',  12,  5)->nullable();
            $table->string('U/M', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foremind_final');
    }
};
