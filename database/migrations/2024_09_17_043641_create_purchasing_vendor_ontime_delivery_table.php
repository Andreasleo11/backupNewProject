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
        Schema::create('purchasing_vendor_ontime_delivery', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code');
            $table->string('vendor_name');
            $table->string('item_code');
            $table->string('description');
            $table->date('request_date');
            $table->integer('request_quantity');
            $table->date('actual_date');
            $table->integer('actual_incoming_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_vendor_ontime_delivery');
    }
};
