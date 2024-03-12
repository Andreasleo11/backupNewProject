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
        Schema::create('cap_results', function (Blueprint $table) {
            $table->id();
            $table->string("line_code")->nullable();
            $table->string("mould_code")->nullable();
            $table->integer("forecast_qty")->nullable();
            $table->decimal("cycle_time",8,2)->nullable();
            $table->decimal("production_time",10,2)->nullable();
            $table->decimal("balance",10,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cap_results');
    }
};
