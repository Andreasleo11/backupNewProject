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
        Schema::dropIfExists('overtime_form_approvals');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('overtime_form_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('overtime_form_id');
            $table->unsignedBigInteger('flow_step_id');
            $table->string('status', 50)->default('pending');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_path')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }
};
