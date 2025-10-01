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
        Schema::create('form_kerusakan', function (Blueprint $table) {
            $table->id();
            $table->string('doc_num')->nullable();
            $table->string('customer')->nullable();
            $table->date('release_date')->nullable();
            $table->string('nama_barang')->nullable();
            $table->string('proses')->nullable();
            $table->string('masalah')->nullable();
            $table->string('sebab')->nullable();
            $table->string('penanggulangan')->nullable();
            $table->string('pic')->nullable();
            $table->string('target')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_kerusakan');
    }
};
