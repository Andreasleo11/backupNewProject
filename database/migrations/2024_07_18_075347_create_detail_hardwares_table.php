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
        Schema::create('detail_hardwares', function (Blueprint $table) {
            $table->id();
            $table->integer('master_inventory_id');
            $table->integer('hardware_id');
            $table->string('brand');
            $table->string('hardware_name');
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_hardwares');
    }
};
