<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `surat_perintah_kerja` CHANGE `tanggal_terima` `tanggal_mulai` DATETIME null",
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `surat_perintah_kerja` CHANGE `tanggal_mulai` `tanggal_terima` DATETIME null",
        );
    }
};
