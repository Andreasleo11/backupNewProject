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
        Schema::table('approvals_rule_templates', function (Blueprint $table) {
            // Versioning columns
            $table->uuid('version_uuid')->nullable()->after('id');
            $table->unsignedInteger('version_number')->default(1)->after('version_uuid');
            $table->boolean('is_current')->default(true)->after('version_number');
            $table->foreignId('parent_version_id')->nullable()->constrained('approvals_rule_templates')->nullOnDelete();
            $table->text('version_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Indexes
            $table->index(['version_uuid', 'version_number']);
            $table->index(['version_uuid', 'is_current']);
        });

        // Update approval_requests to reference specific version
        Schema::table('approval_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('approval_requests', 'rule_template_version_id')) {
                $table->foreignId('rule_template_version_id')->nullable()->after('rule_template_id');

                $table->foreign('rule_template_version_id')
                    ->references('id')
                    ->on('approvals_rule_templates')
                    ->nullOnDelete();

                $table->index('rule_template_version_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove from approval_requests first
        Schema::table('approval_requests', function (Blueprint $table) {
            if (Schema::hasColumn('approval_requests', 'rule_template_version_id')) {
                $table->dropIndex(['rule_template_version_id']);
                $table->dropForeign(['rule_template_version_id']);
                $table->dropColumn('rule_template_version_id');
            }
        });

        // Remove from rule_templates
        Schema::table('approvals_rule_templates', function (Blueprint $table) {
            $table->dropIndex(['version_uuid', 'is_current']);
            $table->dropIndex(['version_uuid', 'version_number']);
            $table->dropForeign(['parent_version_id']);
            $table->dropColumn(['version_uuid', 'version_number', 'is_current', 'parent_version_id', 'version_notes', 'created_by']);
        });
    }
};
