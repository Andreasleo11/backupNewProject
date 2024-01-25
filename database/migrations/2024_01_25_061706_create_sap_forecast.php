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
        Schema::create('sap_forecast', function (Blueprint $table) {
            $table->id();
            $table->string('forecast_code', 255);
            $table->string('forecast_name', 255);
            $table->string('item_no', 255)->nullable();
            $table->date('forecast_date')->nullable();
            $table->integer('quantity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_forecast');
    }
};
