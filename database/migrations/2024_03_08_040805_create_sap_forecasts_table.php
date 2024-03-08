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
        Schema::create('sap_forecasts', function (Blueprint $table) {
            $table->string("forecast_code")->nullable();
            $table->string("forecast_name")->nullable();
            $table->string("item_no")->nullable();
            $table->date("forecast_date")->nullable();
            $table->integer("quantity")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_forecasts');
    }
};
