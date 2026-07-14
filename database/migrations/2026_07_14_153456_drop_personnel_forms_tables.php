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
        Schema::dropIfExists('form_cuti');
        Schema::dropIfExists('form_keluars');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('form_cuti', function (Blueprint $table) {
            $table->id();
            // Stub for rollback
            $table->timestamps();
        });

        Schema::create('form_keluars', function (Blueprint $table) {
            $table->id();
            // Stub for rollback
            $table->timestamps();
        });
    }
};
