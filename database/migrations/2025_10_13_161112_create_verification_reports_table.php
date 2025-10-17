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
        Schema::create('verification_reports', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->foreignId('creator_id')->constrained('users');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('DRAFT'); // use enum in code
            $table->json('meta')->nullable(); // e.g., dept, tags
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_reports');
    }
};
