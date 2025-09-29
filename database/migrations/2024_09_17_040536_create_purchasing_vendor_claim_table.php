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
        Schema::create('purchasing_vendor_claim', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_code');
            $table->string('vendor_name');
            $table->string('item_code');
            $table->string('description');
            $table->string('delivery_no');
            $table->date('incoming_date');
            $table->integer('quantity');
            $table->date('claim_start_date');
            $table->date('claim_finish_date');
            $table->string('can_use');
            $table->string('remarks')->nullable();
            $table->string('reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchasing_vendor_claim');
    }
};
