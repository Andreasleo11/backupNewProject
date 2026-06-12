<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Remove workflow_status and workflow_step columns from purchase_requests table.
     * These are now computed attributes that delegate to approval_request relationship.
     */
    public function up(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requests', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
            if (Schema::hasColumn('purchase_requests', 'workflow_step')) {
                $table->dropColumn('workflow_step');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'workflow_status')) {
                $table->string('workflow_status')->nullable();
            }
            if (!Schema::hasColumn('purchase_requests', 'workflow_step')) {
                $table->string('workflow_step')->nullable();
            }
        });
    }
};
