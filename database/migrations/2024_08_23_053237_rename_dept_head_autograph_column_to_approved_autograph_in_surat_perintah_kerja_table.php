<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_perintah_kerja', function (Blueprint $table) {
            DB::statement('ALTER TABLE `surat_perintah_kerja` CHANGE `dept_head_autograph` `approved_autograph` VARCHAR(255) null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_perintah_kerja', function (Blueprint $table) {
            DB::statement('ALTER TABLE `surat_perintah_kerja` CHANGE `approved_autograph` `dept_head_autograph` VARCHAR(255) null');
        });
    }
};
