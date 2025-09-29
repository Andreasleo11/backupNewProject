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
        Schema::create('evaluation_datas', function (Blueprint $table) {
            $table->id();
            $table->string('NIK');
            $table->date('Month');
            $table->integer('Alpha')->default(0);
            $table->integer('Telat')->default(0);
            $table->integer('Izin')->default(0);
            $table->integer('kerajinan_kerja')->nullable()->default(0);
            $table->integer('kerapian_pakaian')->nullable()->default(0);
            $table->integer('kerapian_rambut')->nullable()->default(0);
            $table->integer('kerapian_sepatu')->nullable()->default(0);
            $table->integer('prestasi')->nullable()->default(0);
            $table->integer('loyalitas')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_datas');
    }
};
