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
        Schema::create('prodplan_asm_delraws', function (Blueprint $table) {
            $table->id();
            $table->date('delivery_date')->nullable();
            $table->integer('process_owner')->nullable();
            $table->integer('bom_level')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_pair')->nullable();
            $table->string('item_fg')->nullable();
            $table->string('asm_on_line')->nullable();
            $table->string('fg_code_line')->nullable();
            $table->integer('quantity')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodplan_asm_delraws');
    }
};
