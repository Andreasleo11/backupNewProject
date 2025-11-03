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
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g., ORG_STRUCTURE
            $table->string('name'); // e.g., Organization Structure
            $table->text('description')->nullable();
            $table->json('allowed_mimetypes')->nullable(); // e.g., ["application/pdf","image/png","image/jpeg"]
            $table->unsignedInteger('min_count')->default(1); // how many files required at minimum
            $table->unsignedInteger('validity_days')->nullable(); // if set, file expires after N days from valid_from
            $table->enum('frequency', ['once', 'yearly', 'quarterly', 'monthly'])->default('once');
            $table->boolean('requires_approval')->default(false); // if true, files must be approved to count
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
