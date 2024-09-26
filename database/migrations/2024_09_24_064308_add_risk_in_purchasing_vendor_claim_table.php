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
        Schema::table('purchasing_vendor_claim', function (Blueprint $table) {
            $table->string('risk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchasing_vendor_claim', function (Blueprint $table) {
            $table->dropColumn('risk');
        });
    }
};
