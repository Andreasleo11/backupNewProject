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
            $table->dropColumn(['workflow_status', 'workflow_step']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('workflow_status')->nullable();
            $table->string('workflow_step')->nullable();
        });
    }
};
