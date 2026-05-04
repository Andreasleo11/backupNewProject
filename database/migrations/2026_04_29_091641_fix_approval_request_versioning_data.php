<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix approval request versioning data to use proper UUIDs
     *
     * The system was designed to store version group UUIDs in rule_template_id,
     * but the database column was created as an integer foreign key.
     * This migration:
     * 1. Changes rule_template_id column to VARCHAR to store UUIDs
     * 2. Updates existing data to use proper UUIDs
     * 3. Removes the incorrect foreign key constraint
     */
    public function up(): void
    {
        // Drop the foreign key constraint first (if it exists)
        try {
            DB::statement('ALTER TABLE approval_requests DROP FOREIGN KEY approval_requests_rule_template_id_foreign');
        } catch (\Exception $e) {
            // Constraint might not exist or have different name, continue
        }

        // Change the column type from integer to string to store UUIDs
        DB::statement('ALTER TABLE approval_requests MODIFY COLUMN rule_template_id VARCHAR(36) NULL');

        // Now fix the data to use UUIDs instead of IDs
        DB::statement('
            UPDATE approval_requests ar
            JOIN approvals_rule_templates rt ON ar.rule_template_version_id = rt.id
            SET ar.rule_template_id = rt.version_uuid
            WHERE ar.rule_template_version_id IS NOT NULL
            AND rt.version_uuid IS NOT NULL
        ');

        // Log the changes for audit purposes
        $fixedCount = DB::select('
            SELECT COUNT(*) as count FROM approval_requests ar
            JOIN approvals_rule_templates rt ON ar.rule_template_version_id = rt.id
            WHERE ar.rule_template_id = rt.version_uuid
        ')[0]->count;

        echo "Fixed {$fixedCount} approval requests to use proper UUID versioning\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert data back to storing IDs first
        DB::statement('
            UPDATE approval_requests ar
            SET ar.rule_template_id = ar.rule_template_version_id
            WHERE ar.rule_template_version_id IS NOT NULL
        ');

        // Change back to integer
        DB::statement('ALTER TABLE approval_requests MODIFY COLUMN rule_template_id BIGINT UNSIGNED NULL');

        // Restore foreign key constraint
        DB::statement('
            ALTER TABLE approval_requests
            ADD CONSTRAINT approval_requests_rule_template_id_foreign
            FOREIGN KEY (rule_template_id) REFERENCES approvals_rule_templates(id) ON DELETE SET NULL
        ');

        echo "Reverted approval requests to use ID-based referencing\n";
    }
};
