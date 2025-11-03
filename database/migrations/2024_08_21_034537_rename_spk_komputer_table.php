<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('surat_perintah_kerja_komputer', 'surat_perintah_kerja');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('surat_perintah_kerja', 'surat_perintah_kerja_komputer');
    }
};
