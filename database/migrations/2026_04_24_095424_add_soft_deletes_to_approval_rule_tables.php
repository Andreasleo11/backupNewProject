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
            $table->softDeletes();
        });

        Schema::table('approvals_rule_step_templates', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals_rule_step_templates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('approvals_rule_templates', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
