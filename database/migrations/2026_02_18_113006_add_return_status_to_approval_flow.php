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
        // Add 'RETURNED' to approval_requests.status enum
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN status ENUM('DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED', 'RETURNED') DEFAULT 'DRAFT'");

        // Add 'return_reason' to approval_steps table
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->text('return_reason')->nullable()->after('remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum (warning: data loss if records exist with RETURNED status)
        // ideally we'd update them to something else first, but for rollback we'll just strict revert schema
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN status ENUM('DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED') DEFAULT 'DRAFT'");

        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropColumn('return_reason');
        });
    }
};
