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
        Schema::create("prodplan_asm_items", function (Blueprint $table) {
            $table->id();
            $table->integer("order_prod")->nullable();
            $table->string("status")->nullable();
            $table->string("item_code")->nullable();
            $table->string("pair_code")->nullable();
            $table->string("material_group")->nullable();
            $table->string("temporary_value")->nullable();
            $table->string("machine_selected")->nullable();
            $table->integer("bom_level")->nullable();
            $table->integer("continue_prod")->nullable();
            $table->integer("lead_time")->nullable();
            $table->integer("safety_stock")->nullable();
            $table->integer("daily_limit")->nullable();
            $table->integer("prod_min")->nullable();
            $table->decimal("cycle_time_raw", 8, 5)->nullable();
            $table->integer("cavity")->nullable();
            $table->decimal("cycle_time", 8, 5)->nullable();
            $table->integer("man_power")->nullable();
            $table->integer("setup_time")->nullable();
            $table->integer("total_delivery")->nullable();
            $table->integer("total_forecast")->nullable();
            $table->integer("total_pps")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("prodplan_asm_items");
    }
};
