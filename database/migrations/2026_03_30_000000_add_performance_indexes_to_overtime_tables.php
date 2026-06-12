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
        $headerIndexes = ['user_id', 'dept_id', 'is_push', 'status'];
        foreach ($headerIndexes as $index) {
            try {
                Schema::table('header_form_overtime', function (Blueprint $table) use ($index) {
                    $table->dropIndex([$index]);
                });
            } catch (\Exception $e) {}
        }

        $detailIndexes = ['header_id', 'start_date'];
        foreach ($detailIndexes as $index) {
            try {
                Schema::table('detail_form_overtime', function (Blueprint $table) use ($index) {
                    $table->dropIndex([$index]);
                });
            } catch (\Exception $e) {}
        }

        try {
            Schema::table('detail_form_overtime', function (Blueprint $table) {
                $table->dropIndex(['status', 'is_processed']);
            });
        } catch (\Exception $e) {}
    }
};
