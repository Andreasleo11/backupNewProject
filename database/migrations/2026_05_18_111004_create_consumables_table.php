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
        Schema::create('consumables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->foreignId('category_id')->constrained('consumable_categories')->onDelete('cascade');
            $table->integer('current_stock')->default(0);
            $table->integer('min_stock')->default(5);
            $table->string('unit')->nullable();
            $table->integer('reorder_point')->nullable();
            $table->foreignId('location_id')->nullable()->constrained('asset_locations')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumables');
    }
};
