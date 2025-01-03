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
        Schema::create('waiting_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('mold_name');
            $table->string('capture_photo_path');
            $table->string('process');
            $table->double('price');
            $table->string('quotation_number');
            $table->string('remark');
            $table->integer('status')->default(1); // Default value of 1;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waiting_purchase_orders');
    }
};
