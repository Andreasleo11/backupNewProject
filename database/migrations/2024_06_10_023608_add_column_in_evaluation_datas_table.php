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
        Schema::table('evaluation_datas', function (Blueprint $table) {
            $table->string('kemampuan_kerja')->nullable()->default('C');
            $table->string('kecerdasan_kerja')->nullable()->default('C');
            $table->string('qualitas_kerja')->nullable()->default('C');
            $table->string('disiplin_kerja')->nullable()->default('C');
            $table->string('kepatuhan_kerja')->nullable()->default('C');
            $table->string('lembur')->nullable()->default('C');
            $table->string('efektifitas_kerja')->nullable()->default('C');
            $table->string('relawan')->nullable()->default('C');
            $table->string('integritas')->nullable()->default('C');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_datas', function (Blueprint $table) {
            $table->dropColumn('kemampuan_kerja');
            $table->dropColumn('kecerdasan_kerja');
            $table->dropColumn('qualitas_kerja');
            $table->dropColumn('disiplin_kerja');
            $table->dropColumn('kepatuhan_kerja');
            $table->dropColumn('lembur');
            $table->dropColumn('efektifitas_kerja');
            $table->dropColumn('relawan');
            $table->dropColumn('integritas');
        });
    }
};
