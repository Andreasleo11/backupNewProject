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
        Schema::create('surat_perintah_kerja_komputer', function (Blueprint $table) {
            $table->id();
            $table->string('no_dokumen');
            $table->string('pelapor');
            $table->string('dept');
            $table->datetime('tanggal_lapor');
            $table->string('judul_laporan');
            $table->string('keterangan_laporan');
            $table->string('pic')->nullable();
            $table->string('tindakan')->nullable();
            $table->integer('status_laporan')->nullable();
            $table->datetime('tanggal_selesai')->nullable();
            $table->datetime('tanggal_estimasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_perintah_kerja_komputer');
    }
};
