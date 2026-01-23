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
        Schema::table('employees', function (Blueprint $table) {
            $table->index('Dept', 'idx_emp_dept');
            $table->index('status', 'idx_emp_status');
            $table->index(['Dept', 'status'], 'idx_emp_dept_status');
            $table->index('start_date', 'idx_emp_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_emp_dept');
            $table->dropIndex('idx_emp_status');
            $table->dropIndex('idx_emp_dept_status');
            $table->dropIndex('idx_emp_start_date');
        });
    }
};
