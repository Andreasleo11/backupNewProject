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
        Schema::table('form_kerusakan', function (Blueprint $table) {
            $table->dropColumn('doc_num');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_kerusakan', function (Blueprint $table) {
            $table->string('doc_num')->nullable();
        });
    }
};