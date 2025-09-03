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
            "ALTER TABLE `surat_perintah_kerja` CHANGE `keterangan_pic` `tindakan` VARCHAR(255) null",
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `surat_perintah_kerja` CHANGE `tindakan` `keterangan_pic` VARCHAR(255) null",
        );
    }
};
