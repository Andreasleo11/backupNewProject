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
        Schema::create('cap_items', function (Blueprint $table) {
            $table->id();
            $table->string("item_code")->nullable();
            $table->string("line_category")->nullable();
            $table->integer("departement")->nullable();
            $table->integer("quantity")->nullable();
            $table->decimal("cycle_time_raw", 8,5)->nullable();
            $table->decimal("cycle_time", 8,5)->nullable();
            $table->integer("cavity")->nullable();
            $table->integer("man_power")->nullable();
            $table->string("pair")->nullable();
            $table->decimal("total_forecast_time", 10,2)->nullable();
            $table->integer("counter_forecast")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cap_items');
    }
};
