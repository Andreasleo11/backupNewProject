<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE `surat_perintah_kerja` CHANGE `dept` `from_department` VARCHAR(255)',
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'ALTER TABLE `surat_perintah_kerja` CHANGE `from_department` `dept` VARCHAR(255)',
        );
    }
};
