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
        Schema::create('stock_transaction', function (Blueprint $table) {
            $table->id();
            $table->string('barcode_string');
            $table->integer('stock_id');
            $table->integer('dept_id')->nullable();
            $table->datetime('in_time');
            $table->boolean('is_out')->default(false);
            $table->boolean('is_return')->default(false);
            $table->string('receiver')->nullable();
            $table->string('remark')->nullable();
            $table->datetime('out_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transaction');
    }
};
