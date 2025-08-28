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
        Schema::create("form_cuti", function (Blueprint $table) {
            $table->id();
            $table->string("doc_num")->unique();
            $table->string("name");
            $table->string("jabatan")->nullable();
            $table->string("department");
            $table->string("jenis_cuti");
            $table->string("pengganti")->nullable();
            $table->string("keperluan");
            $table->date("tanggal_masuk")->nullable();
            $table->string("no_karyawan");
            $table->date("tanggal_permohonan");
            $table->date("mulai_tanggal");
            $table->date("sampai_tanggal");
            $table->string("keterangan_user")->bool();
            $table->string("is_accept")->bool();
            $table->string("waktu_cuti");
            $table->string("autograph_1")->nullable();
            $table->string("autograph_user_1")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("form_cuti");
    }
};
