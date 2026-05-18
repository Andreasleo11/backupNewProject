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
        Schema::create('stock_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consumable_id')->constrained('consumables')->onDelete('cascade');
            $table->string('type'); // In, Out
            $table->integer('quantity');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Who did it
            $table->foreignId('target_user_id')->nullable()->constrained('users')->onDelete('set null'); // For issuance
            $table->string('notes')->nullable();
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transactions');
    }
};
