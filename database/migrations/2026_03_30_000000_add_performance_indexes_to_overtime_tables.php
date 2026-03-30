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
        Schema::table('header_form_overtime', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('dept_id');
            $table->index('is_push');
            $table->index('status');
        });

        Schema::table('detail_form_overtime', function (Blueprint $table) {
            $table->index('header_id');
            $table->index(['status', 'is_processed']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('header_form_overtime', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['dept_id']);
            $table->dropIndex(['is_push']);
            $table->dropIndex(['status']);
        });

        Schema::table('detail_form_overtime', function (Blueprint $table) {
            $table->dropIndex(['header_id']);
            $table->dropIndex(['status', 'is_processed']);
            $table->dropIndex(['start_date']);
        });
    }
};
