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
        Schema::table('master_po', function (Blueprint $table) {
            $table->renameColumn('po_date', 'invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_po', function (Blueprint $table) {
            $table->renameColumn('invoice_date', 'po_date');
        });
    }
};
