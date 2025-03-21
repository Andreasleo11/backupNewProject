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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('purchase_order_number');
            $table->string('code');
            $table->string('name');
            $table->string('category_name');
            $table->string('category_code');
            $table->string('uom')->nullable();
            $table->integer('quantity');
            $table->string('currency');
            $table->float('price');
            $table->string('dept_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
