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
        Schema::create('sap_fct_bom_wip_third', function (Blueprint $table) {
            $table->id();
            $table->string('fg_code', 255);
            $table->string('semi_first', 255)->nullable();
            $table->string('semi_second', 255)->nullable();
            $table->string('semi_third', 255)->nullable();
            $table->integer('level')->nullable();
            $table->integer('bom_quantity')->nullable();
            $table->integer('item_group')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_fct_bom_wip_third');
    }
};
