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
        Schema::create('mtc_mould_downs', function (Blueprint $table) {
            $table->id();
            $table->integer("mould_code")->nullable();
            $table->date("date_down")->nullable();
            $table->date("date_prediction")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mtc_mould_downs');
    }
};
