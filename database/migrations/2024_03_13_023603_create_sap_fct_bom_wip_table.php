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
        Schema::create('sap_fct_bom_wip', function (Blueprint $table) {
            $table->string('fg_code');
            $table->string('semi_first')->nullable();
            $table->string('semi_second')->nullable();
            $table->string('semi_third')->nullable();
            $table->integer('level')->nullable();
            $table->integer('bom_quantity')->nullable();
            $table->integer('item_group')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sap_fct_bom_wip');
    }
};
