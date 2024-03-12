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
        Schema::create('cap_forecasts', function (Blueprint $table) {
            $table->string("item_code")->nullable();
            $table->integer("quantity")->nullable();
            $table->integer("quantity_next")->nullable();
            $table->integer("total")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cap_forecasts');
    }
};
