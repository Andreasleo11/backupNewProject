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
        Schema::create('prodplan_snd_delitems', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable();
            $table->integer('item_bom_level')->nullable();
            $table->string('item_pair')->nullable();
            $table->integer('pair_bom_level')->nullable();
            $table->string('item_wip')->nullable();
            $table->integer('bom_level')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prodplan_snd_delitems');
    }
};
