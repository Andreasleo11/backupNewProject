<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove redundant approval_request_id column from purchase_orders table.
     *
     * The approval request already stores the relationship through polymorphic
     * approvable_type and approvable_id fields, making the foreign key redundant.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['approval_request_id']);

            // Then drop indexes
            $table->dropIndex('idx_po_approval_request');
            $table->dropIndex('idx_po_status_approval');

            // Finally drop the redundant column
            $table->dropColumn('approval_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Re-add the column
            $table->foreignId('approval_request_id')
                ->nullable()
                ->constrained('approval_requests')
                ->nullOnDelete()
                ->after('revision_count');

            // Re-add indexes
            $table->index('approval_request_id', 'idx_po_approval_request');
            $table->index(['status', 'approval_request_id'], 'idx_po_status_approval');
        });
    }
};
