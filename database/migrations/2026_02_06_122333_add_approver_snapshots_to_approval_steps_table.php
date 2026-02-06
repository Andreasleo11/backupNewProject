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
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->string('approver_snapshot_name', 100)->nullable()->after('approver_id');
            $table->string('approver_snapshot_role_slug', 100)->nullable()->after('approver_snapshot_name');
            $table->string('approver_snapshot_label', 150)->nullable()->after('approver_snapshot_role_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_steps', function (Blueprint $table) {
            $table->dropColumn(['approver_snapshot_name', 'approver_snapshot_role_slug', 'approver_snapshot_label']);
        });
    }
};
