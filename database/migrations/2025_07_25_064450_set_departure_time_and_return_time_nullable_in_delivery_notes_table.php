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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->time('departure_time')->nullable()->change();
            $table->time('return_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->time('departure_time')->nullable(false)->change();
            $table->time('return_time')->nullable(false)->change();
        });
    }
};
