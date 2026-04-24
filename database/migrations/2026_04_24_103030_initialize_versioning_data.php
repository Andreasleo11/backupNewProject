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
        // Assign version_uuid to existing rules that don't have one
        DB::statement('
            UPDATE approvals_rule_templates 
            SET version_uuid = UUID(),
                version_number = 1,
                is_current = 1
            WHERE version_uuid IS NULL
        ');

        // Update existing approval_requests to reference the rule version
        // Since existing records should reference the "current" version (which we just set as version 1)
        DB::statement('
            UPDATE approval_requests ar
            JOIN approvals_rule_templates rt ON ar.rule_template_id = rt.id
            SET ar.rule_template_version_id = rt.id
            WHERE ar.rule_template_version_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear version_uuid assignments (optional - usually we don't reverse data changes)
        // DB::statement("UPDATE approvals_rule_templates SET version_uuid = NULL, version_number = NULL, is_current = NULL");
        // DB::statement("UPDATE approval_requests SET rule_template_version_id = NULL");
    }
};
