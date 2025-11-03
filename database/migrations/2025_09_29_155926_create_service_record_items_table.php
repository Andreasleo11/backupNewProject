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
        Schema::create('service_record_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('part_id')->nullable()->constrained('service_part_catalog')->nullOnDelete();
            $table->string('part_name'); // denormalized for history (editable)
            $table->enum('action', ['checked', 'replaced', 'repaired', 'topped_up', 'cleaned']);
            $table->unsignedTinyInteger('condition_before')->nullable(); // 1-5 scale
            $table->unsignedTinyInteger('condition_after')->nullable();
            $table->decimal('qty', 10, 2)->nullable();
            $table->string('uom')->nullable(); // pcs, liter, etc.
            $table->decimal('unit_cost', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->index(['part_name', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_record_items');
    }
};
