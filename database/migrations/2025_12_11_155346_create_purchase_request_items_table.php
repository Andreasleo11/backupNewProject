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
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('master_item_id')->nullable(); // if you have a master-items table
            $table->string('item_name');
            $table->decimal('quantity', 15, 3);
            $table->string('uom', 20);
            $table->string('currency', 10)->default('IDR');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->text('purpose')->nullable();
            $table->decimal('received_quantity', 15, 3)->nullable();
            $table->timestamps();

            $table->foreign('purchase_request_id')
                ->references('id')->on('purchase_requests')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
