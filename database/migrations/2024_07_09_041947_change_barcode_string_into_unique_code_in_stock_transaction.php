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
        Schema::table('stock_transaction', function (Blueprint $table) {
            DB::statement('ALTER TABLE stock_transaction CHANGE barcode_string unique_code VARCHAR(255)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_transaction', function (Blueprint $table) {
            DB::statement('ALTER TABLE stock_transaction CHANGE unique_code barcode_string VARCHAR(255)');
        });
    }
};
