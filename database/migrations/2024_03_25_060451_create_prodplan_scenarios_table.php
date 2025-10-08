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
        Schema::create('prodplan_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('setup_name')->nullable();
            $table->integer('val_int_inj')->nullable();
            $table->string('val_vc_inj')->nullable();
            $table->integer('val_int_snd')->nullable();
            $table->string('val_vc_snd')->nullable();
            $table->integer('val_int_asm')->nullable();
            $table->string('val_vc_asm')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodplan_scenarios');
    }
};
