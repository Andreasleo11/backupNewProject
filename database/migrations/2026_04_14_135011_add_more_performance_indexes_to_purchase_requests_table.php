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
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Add indexes for commonly filtered columns
            $table->index('date_pr', 'idx_pr_date_pr');
            $table->index('from_department', 'idx_pr_from_department');
            $table->index('to_department', 'idx_pr_to_department');
            $table->index('branch', 'idx_pr_branch');
            $table->index(['user_id_create', 'created_at'], 'idx_pr_creator_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropIndex('idx_pr_date_pr');
            $table->dropIndex('idx_pr_from_department');
            $table->dropIndex('idx_pr_to_department');
            $table->dropIndex('idx_pr_branch');
            $table->dropIndex('idx_pr_creator_date');
        });
    }
};
