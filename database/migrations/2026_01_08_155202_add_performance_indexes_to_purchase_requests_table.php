<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Add indexes for commonly filtered/joined columns
            $table->index('status', 'idx_pr_status');
            $table->index('created_at', 'idx_pr_created_at');
            $table->index(['status', 'created_at'], 'idx_pr_status_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $indexes = [
            'idx_pr_status',
            'idx_pr_created_at',
            'idx_pr_status_date'
        ];

        foreach ($indexes as $index) {
            try {
                Schema::table('purchase_requests', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index);
                });
            } catch (\Exception $e) {}
        }
    }
};
