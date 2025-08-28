<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("form_keluars", function (Blueprint $table) {
            $table->id();
            $table->string("doc_num")->unique();
            $table->string("name");
            $table->string("jabatan")->nullable();
            $table->string("department");
            $table->string("alasan_izin_keluar");
            $table->string("pengganti");
            $table->string("keperluan");
            $table->string("tanggal_masuk");
            $table->string("no_karyawan");
            $table->string("tanggal_permohonan");
            $table->string("keterangan_user")->bool();
            $table->string("autograph_1")->nullable();
            $table->string("autograph_user_1")->nullable();
            $table->string("waktu_keluar");
            $table->time("jam_keluar");
            $table->time("jam_kembali");
            $table->string("is_accept")->bool()->nullable();
            $table->string("is_security")->bool()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("form_keluars");
    }
};
