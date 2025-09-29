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
        Schema::create('approval_flow_rules', function (Blueprint $table) {
            $table->id();
            // NULL means “wild-card / ignore this column when matching”
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->string('branch')->nullable(); // e.g. "KARAWANG"
            $table->boolean('is_design')->nullable(); // null = ignore
            $table->foreignId('approval_flow_id')->constrained('approval_flows');

            $table->unsignedTinyInteger('priority')->default(99); // lower = match first
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_flow_rules');
    }
};
