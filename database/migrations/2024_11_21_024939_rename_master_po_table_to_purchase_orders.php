<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('master_po', 'purchase_orders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('purchase_orders', 'master_po');
    }
};
