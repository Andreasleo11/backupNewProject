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
        Schema::table('surat_perintah_kerja_komputer', function (Blueprint $table) {
            $table->softDeletes();
            $table->datetime('tanggal_terima')->nullable()->after('status_laporan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_perintah_kerja_komputer', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('tanggal_terima');
        });
    }
};
