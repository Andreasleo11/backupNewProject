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
        Schema::create('overtime_form_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('overtime_form_id')->constrained('header_form_overtime')->cascadeOnDelete();
            $table->foreignId('flow_step_id')->constrained('approval_flow_steps');
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->string('signature_path')->nullable();
            $table->string('status');
            $table->timestamp('signed_at')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['overtime_form_id', 'flow_step_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtime_form_approvals');
    }
};
