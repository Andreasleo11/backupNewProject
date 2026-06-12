<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add approval relationship to purchase orders table.
     * This establishes the formal relationship between POs and approval workflows.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add nullable foreign key to approval_requests
            $table->foreignId('approval_request_id')
                ->nullable()
                ->constrained('approval_requests')
                ->nullOnDelete()
                ->after('revision_count');

            // Add indexes for performance
            $table->index('approval_request_id', 'idx_po_approval_request');
            $table->index(['status', 'approval_request_id'], 'idx_po_status_approval');

            // Add check constraint for status enum values (if not exists)
            // Note: This might fail if constraint already exists from previous migration
            try {
                DB::statement('ALTER TABLE purchase_orders ADD CONSTRAINT po_status_enum_check CHECK (status >= 1 AND status <= 5)');
            } catch (\Exception $e) {
                // Constraint might already exist, continue
                Log::info('Status check constraint may already exist: ' . $e->getMessage());
            }
        });

        // Add composite index on approval_requests for reverse lookups
        Schema::table('approval_requests', function (Blueprint $table) {
            // Index for polymorphic lookups
            $table->index(['approvable_type', 'approvable_id'], 'idx_approval_polymorphic_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove index safely
        try {
            Schema::table('approval_requests', function (Blueprint $table) {
                $table->dropIndex('idx_approval_polymorphic_lookup');
            });
        } catch (\Exception $e) {
            Log::info('Index idx_approval_polymorphic_lookup might already be dropped: ' . $e->getMessage());
        }

        // Drop foreign key constraint first before any indexes
        try {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropForeign(['approval_request_id']);
            });
        } catch (\Exception $e) {
            Log::info('Foreign key might already be dropped: ' . $e->getMessage());
        }

        // Drop indexes
        try {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropIndex('idx_po_status_approval');
            });
        } catch (\Exception $e) {
            Log::info('Index idx_po_status_approval might already be dropped: ' . $e->getMessage());
        }

        try {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropIndex('idx_po_approval_request');
            });
        } catch (\Exception $e) {
            Log::info('Index idx_po_approval_request might already be dropped: ' . $e->getMessage());
        }

        // Drop the column
        try {
            Schema::table('purchase_orders', function (Blueprint $table) {
                $table->dropColumn('approval_request_id');
            });
        } catch (\Exception $e) {
            Log::info('Column approval_request_id might already be dropped: ' . $e->getMessage());
        }

        // Remove check constraint if it exists
        try {
            DB::statement('ALTER TABLE purchase_orders DROP CONSTRAINT po_status_enum_check');
        } catch (\Exception $e) {
            // Constraint might not exist, continue
        }
    }
};
