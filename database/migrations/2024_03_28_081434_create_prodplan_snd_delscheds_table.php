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
        Schema::create('prodplan_snd_delscheds', function (Blueprint $table) {
            $table->id();
            $table->date('delivery_date')->nullable();
            $table->date('actual_deldate')->nullable();
            $table->date('process_date')->nullable();
            $table->date('complete_date')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->integer('item_bom_level')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('pair_code')->nullable();
            $table->string('pair_name')->nullable();
            $table->integer('pair_bom_level')->nullable();
            $table->integer('pair_quantity')->nullable();
            $table->string('prior_item_code')->nullable();
            $table->integer('prior_bom_level')->nullable();
            $table->integer('final_quantity')->nullable();
            $table->integer('completed')->nullable();
            $table->integer('outstanding')->nullable();
            $table->integer('status')->nullable();
            $table->string('remarks')->nullable();
            $table->integer('remarks_leadtime')->nullable();
            $table->integer('remarks_actual')->nullable();
            $table->string('color')->nullable();
            $table->integer('upcode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodplan_snd_delscheds');
    }
};
