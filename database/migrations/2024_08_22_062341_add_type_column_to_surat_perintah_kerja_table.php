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
        Schema::table('surat_perintah_kerja', function (Blueprint $table) {
            $table->string('type')->nullable()->after('to_department');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_perintah_kerja', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};