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
        Schema::create('approvals_rule_step_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_template_id')->constrained('approvals_rule_templates')->cascadeOnDelete();
            $table->unsignedInteger('sequence'); // 1..n
            $table->enum('approver_type', ['user', 'role']);
            $table->unsignedBigInteger('approver_id')->nullable(); // user_id OR role_id
            $table->boolean('final')->default(false);
            $table->boolean('parallel_group')->default(false); // future-proofing (parallel steps)
            $table->timestamps();
            $table->unique(['rule_template_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals_rule_step_templates');
    }
};
