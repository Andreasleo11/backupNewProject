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
        Schema::create("purchasing_detail_evaluation_supplier", function (Blueprint $table) {
            $table->id();
            $table->integer("header_id");
            $table->string("month");
            $table->integer("kualitas_barang")->nullable();
            $table->integer("ketepatan_kuantitas_barang")->nullable();
            $table->integer("ketepatan_waktu_pengiriman")->nullable();
            $table->integer("kerjasama_permintaan_mendadak")->nullable();
            $table->integer("respon_klaim")->nullable();
            $table->integer("sertifikasi")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("purchasing_detail_evaluation_supplier");
    }
};
