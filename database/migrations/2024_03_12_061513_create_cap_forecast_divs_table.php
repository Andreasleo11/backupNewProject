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
        Schema::create('cap_forecast_divs', function (Blueprint $table) { 
            $table->string("father_part")->nullable();
            $table->string("item_code")->nullable();
            $table->integer("quantity")->nullable();
            $table->integer("level")->nullable();
            $table->string("paired")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cap_forecast_divs');
    }
};
