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
        // Add 'RETURNED' to approval_steps.status enum
        DB::statement("ALTER TABLE approval_steps MODIFY COLUMN status ENUM('PENDING', 'APPROVED', 'REJECTED', 'SKIPPED', 'RETURNED') DEFAULT 'PENDING'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum (warning: data loss if records exist with RETURNED status)
        DB::statement("ALTER TABLE approval_steps MODIFY COLUMN status ENUM('PENDING', 'APPROVED', 'REJECTED', 'SKIPPED') DEFAULT 'PENDING'");
    }
};
