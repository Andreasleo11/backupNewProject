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
        Schema::create('requirement_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requirement_id')->constrained()->cascadeOnDelete();
            $table->morphs('scope'); // scope_type, scope_id
            $table->boolean('is_mandatory')->default(true);
            $table->date('start_date')->nullable(); // when the requirement starts being enforced
            $table->date('end_date')->nullable(); // optional sunset
            $table->timestamps();
            $table->unique(['requirement_id', 'scope_type', 'scope_id'], 'req_assign_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirement_assignments');
    }
};
