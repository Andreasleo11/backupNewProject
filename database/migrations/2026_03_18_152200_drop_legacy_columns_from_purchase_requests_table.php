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
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Drop legacy autograph image URLs
            $table->dropColumn([
                'autograph_1', 'autograph_2', 'autograph_3', 'autograph_4',
                'autograph_5', 'autograph_6', 'autograph_7',
            ]);

            // Drop legacy autograph user names
            $table->dropColumn([
                'autograph_user_1', 'autograph_user_2', 'autograph_user_3', 'autograph_user_4',
                'autograph_user_5', 'autograph_user_6', 'autograph_user_7',
            ]);

            // Drop legacy step tracking and status
            if (Schema::hasColumn('purchase_requests', 'workflow_step')) {
                $table->dropColumn('workflow_step');
            }
            if (Schema::hasColumn('purchase_requests', 'workflow_status')) {
                $table->dropColumn('workflow_status');
            }
            if (Schema::hasColumn('purchase_requests', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->string('autograph_1')->nullable();
            $table->string('autograph_2')->nullable();
            $table->string('autograph_3')->nullable();
            $table->string('autograph_4')->nullable();
            $table->string('autograph_5')->nullable();
            $table->string('autograph_6')->nullable();
            $table->string('autograph_7')->nullable();

            $table->string('autograph_user_1')->nullable();
            $table->string('autograph_user_2')->nullable();
            $table->string('autograph_user_3')->nullable();
            $table->string('autograph_user_4')->nullable();
            $table->string('autograph_user_5')->nullable();
            $table->string('autograph_user_6')->nullable();
            $table->string('autograph_user_7')->nullable();

            $table->integer('status')->default(8)->nullable();
            $table->string('workflow_status')->nullable();
            $table->string('workflow_step')->nullable();
        });
    }
};
