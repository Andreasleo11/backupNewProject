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
        Schema::create('user_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('label', 100)->nullable(); // e.g. "Primary", "Initials", "Stamp"

            // Signature kind
            $table->enum('kind', ['drawn', 'uploaded', 'text', 'svg']);

            // Stored asset paths (private disk recommended)
            $table->string('file_path')->nullable();   // PNG (transparent)
            $table->string('svg_path')->nullable();    // SVG (if captured/supplied)

            // Tamper-evident hash of the exact stored bytes (PNG recommended)
            $table->char('sha256', 64);

            // Versioning
            $table->boolean('is_default')->default(false);

            // Audit & lifecycle
            $table->timestamp('revoked_at')->nullable();
            $table->json('metadata')->nullable(); // device/ip/ua/canvas size, etc.

            $table->timestamps();

            // Helpful indexes
            $table->index(['user_id', 'is_default']);
            $table->index('sha256');

            // Optional: prevent exact duplicate files per user (uncomment if desired)
            // $table->unique(['user_id', 'sha256']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_signatures');
    }
};
