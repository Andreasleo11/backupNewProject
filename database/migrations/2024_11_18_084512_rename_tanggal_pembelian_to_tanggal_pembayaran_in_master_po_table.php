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
        Schema::table("master_po", function (Blueprint $table) {
            $table->renameColumn("tanggal_pembelian", "tanggal_pembayaran");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("master_po", function (Blueprint $table) {
            $table->renameColumn("tanggal_pembayaran", "tanggal_pembelian");
        });
    }
};
