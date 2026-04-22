<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'CANCELED' to approval_requests.status enum
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN status ENUM('DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED', 'RETURNED', 'CANCELED') DEFAULT 'DRAFT'");

        // Add 'CANCELED' to approval_steps.status enum
        DB::statement("ALTER TABLE approval_steps MODIFY COLUMN status ENUM('PENDING', 'APPROVED', 'REJECTED', 'SKIPPED', 'RETURNED', 'CANCELED') DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum (warning: data loss if records exist with CANCELED status)
        DB::statement("ALTER TABLE approval_requests MODIFY COLUMN status ENUM('DRAFT', 'SUBMITTED', 'IN_REVIEW', 'APPROVED', 'REJECTED', 'RETURNED') DEFAULT 'DRAFT'");

        DB::statement("ALTER TABLE approval_steps MODIFY COLUMN status ENUM('PENDING', 'APPROVED', 'REJECTED', 'SKIPPED', 'RETURNED') DEFAULT 'PENDING'");
    }
};
