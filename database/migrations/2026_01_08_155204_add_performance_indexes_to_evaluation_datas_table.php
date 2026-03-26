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
        Schema::table('evaluation_datas', function (Blueprint $table) {
            $table->index('dept', 'idx_eval_dept');
            $table->index('NIK', 'idx_eval_nik');
            $table->index(['dept', 'Month'], 'idx_eval_dept_month');
            $table->index('depthead', 'idx_eval_depthead');
            $table->index('generalmanager', 'idx_eval_gm');
            $table->index('is_lock', 'idx_eval_is_lock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_datas', function (Blueprint $table) {
            $table->dropIndex('idx_eval_dept');
            $table->dropIndex('idx_eval_nik');
            $table->dropIndex('idx_eval_dept_month');
            $table->dropIndex('idx_eval_depthead');
            $table->dropIndex('idx_eval_gm');
            $table->dropIndex('idx_eval_is_lock');
        });
    }
};
