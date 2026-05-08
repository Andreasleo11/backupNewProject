<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove the check constraint if it exists (MySQL 8.0.16+)
        DB::statement('ALTER TABLE purchase_orders DROP CONSTRAINT IF EXISTS purchase_orders_status_check');

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop the index if it exists
            $table->dropIndex('idx_purchase_orders_status');

            // Make status nullable and remove default
            $table->integer('status')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Revert default status and add index back
            $table->integer('status')->default(1)->change();
            $table->index('status', 'idx_purchase_orders_status');
        });

        // Re-add check constraint
        DB::statement('ALTER TABLE purchase_orders ADD CONSTRAINT purchase_orders_status_check CHECK (status >= 1 AND status <= 5)');
    }
};
