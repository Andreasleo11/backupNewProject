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
        Schema::create('sap_inventory_fg', function (Blueprint $table) {
            $table->string("item_code")->primary();
            $table->string("item_name")->nullable();
            $table->integer("item_group")->nullable();
            $table->integer("day_set_pps")->nullable();
            $table->integer("setup_time")->nullable();
            $table->decimal("cycle_time",8,5)->nullable();
            $table->integer("cavity")->nullable();
            $table->integer("safety_stock")->nullable();
            $table->integer("daily_limit")->nullable();
            $table->integer("stock")->nullable();
            $table->integer("total_spk")->nullable();
            $table->integer("production_min_qty")->nullable();
            $table->integer("standar_packing")->nullable();
            $table->string("pair")->nullable();
            $table->integer("man_power")->nullable();
            $table->string("warehouse")->nullable();
            $table->string("process_owner")->nullable();
            $table->integer("owner_code")->nullable();
            $table->integer("special_condition")->nullable();
            $table->string("fg_code_1")->nullable();
            $table->string("fg_code_2")->nullable();
            $table->string("wip_code")->nullable();
            $table->integer("material_percentage")->nullable();
            $table->integer("continue_production")->nullable();
            $table->string("family")->nullable();
            $table->string("material_group")->nullable();
            $table->string("old_mould")->nullable();
            $table->string("packaging")->nullable();
            $table->integer("bom_level")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_inventory_fg');
    }
};
