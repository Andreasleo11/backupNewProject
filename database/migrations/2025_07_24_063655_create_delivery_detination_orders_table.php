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
        Schema::create('delivery_destination_orders', function (Blueprint $table) {
            $table->id();
            $table
                ->foreignId('delivery_destination_id')
                ->constrained('delivery_destinations')
                ->onDelete('cascade');
            $table->string('delivery_order_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_destination_orders');
    }
};
