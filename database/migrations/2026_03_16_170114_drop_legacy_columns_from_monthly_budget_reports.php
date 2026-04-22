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
        Schema::table('monthly_budget_reports', function (Blueprint $table) {
            $table->dropColumn([
                'is_cancel',
                'cancel_reason',
                'status',
                'is_reject',
                'reject_reason',
                'created_autograph',
                'is_known_autograph',
                'approved_autograph',
            ]);
        });

        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            $table->dropColumn([
                'is_cancel',
                'cancel_reason',
                'status',
                'is_reject',
                'reject_reason',
                'created_autograph',
                'is_known_autograph',
                'approved_autograph',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_budget_reports', function (Blueprint $table) {
            $table->boolean('is_cancel')->default(false);
            $table->text('cancel_reason')->nullable();
            $table->integer('status')->nullable();
            $table->boolean('is_reject')->default(false);
            $table->text('reject_reason')->nullable();
            $table->string('created_autograph')->nullable();
            $table->string('is_known_autograph')->nullable();
            $table->string('approved_autograph')->nullable();
        });

        Schema::table('monthly_budget_summary_reports', function (Blueprint $table) {
            $table->boolean('is_cancel')->default(false);
            $table->text('cancel_reason')->nullable();
            $table->integer('status')->nullable();
            $table->boolean('is_reject')->default(false);
            $table->text('reject_reason')->nullable();
            $table->string('created_autograph')->nullable();
            $table->string('is_known_autograph')->nullable();
            $table->string('approved_autograph')->nullable();
        });
    }
};
