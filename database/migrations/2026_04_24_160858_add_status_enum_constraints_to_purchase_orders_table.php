<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add enum-based status constraints to ensure data integrity.
     * Status values: 1=DRAFT, 2=WAITING, 3=APPROVED, 4=REJECTED, 5=CANCELLED
     */
    public function up(): void
    {
        // Add check constraint for status enum values using raw SQL
        // MySQL 8.0.16+ supports CHECK constraints
        DB::statement('ALTER TABLE purchase_orders ADD CONSTRAINT purchase_orders_status_check CHECK (status >= 1 AND status <= 5)');

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add index for status queries (if not already exists)
            $table->index('status', 'idx_purchase_orders_status');

            // Update default status to DRAFT (1) for new records
            $table->integer('status')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop check constraint using raw SQL (MySQL does not support IF EXISTS here)
        try {
            DB::statement('ALTER TABLE purchase_orders DROP CONSTRAINT purchase_orders_status_check');
        } catch (\Exception $e) {
            // Constraint might not exist, or syntax might be slightly different in older MySQL versions
            try {
                DB::statement('ALTER TABLE purchase_orders DROP CHECK purchase_orders_status_check');
            } catch (\Exception $e2) {
                // Ignore if it still fails
            }
        }

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop the index
            try {
                $table->dropIndex('idx_purchase_orders_status');
            } catch (\Exception $e) {
                // Ignore if index doesn't exist
            }

            // Revert default status
            $table->integer('status')->default(null)->change();
        });
    }
};
