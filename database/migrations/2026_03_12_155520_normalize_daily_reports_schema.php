<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Employee Daily Reports
        $columns = DB::select("SHOW COLUMNS FROM employee_daily_reports");
        $existing = array_column($columns, 'Field');

        foreach (['report_type', 'departement_id', 'employee_name'] as $col) {
            if (in_array($col, $existing)) {
                DB::statement("ALTER TABLE employee_daily_reports DROP COLUMN $col");
            }
        }

        // Add index if not exists
        $indexes = DB::select("SHOW INDEX FROM employee_daily_reports WHERE Column_name = 'employee_id'");
        if (empty($indexes)) {
            DB::statement("ALTER TABLE employee_daily_reports ADD INDEX (employee_id)");
        }

        // 2. Employee Daily Report Logs
        $columns = DB::select("SHOW COLUMNS FROM employee_daily_report_logs");
        $existing = array_column($columns, 'Field');

        foreach (['report_type', 'department_id', 'employee_name'] as $col) {
            if (in_array($col, $existing)) {
                DB::statement("ALTER TABLE employee_daily_report_logs DROP COLUMN $col");
            }
        }

        // Add index if not exists
        $indexes = DB::select("SHOW INDEX FROM employee_daily_report_logs WHERE Column_name = 'employee_id'");
        if (empty($indexes)) {
            DB::statement("ALTER TABLE employee_daily_report_logs ADD INDEX (employee_id)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore columns for employee_daily_reports
        $columns = DB::select("SHOW COLUMNS FROM employee_daily_reports");
        $existing = array_column($columns, 'Field');
        
        if (!in_array('report_type', $existing)) {
            DB::statement("ALTER TABLE employee_daily_reports ADD COLUMN report_type VARCHAR(255) NULL AFTER submitted_at");
        }
        if (!in_array('departement_id', $existing)) {
            DB::statement("ALTER TABLE employee_daily_reports ADD COLUMN departement_id VARCHAR(255) NULL AFTER employee_id");
        }
        if (!in_array('employee_name', $existing)) {
            DB::statement("ALTER TABLE employee_daily_reports ADD COLUMN employee_name VARCHAR(255) NULL AFTER departement_id");
        }

        // Restore columns for employee_daily_report_logs
        $columns = DB::select("SHOW COLUMNS FROM employee_daily_report_logs");
        $existing = array_column($columns, 'Field');

        if (!in_array('report_type', $existing)) {
            DB::statement("ALTER TABLE employee_daily_report_logs ADD COLUMN report_type VARCHAR(255) NULL AFTER work_time");
        }
        if (!in_array('department_id', $existing)) {
            DB::statement("ALTER TABLE employee_daily_report_logs ADD COLUMN department_id VARCHAR(255) NULL AFTER employee_name");
        }
        if (!in_array('employee_name', $existing)) {
            DB::statement("ALTER TABLE employee_daily_report_logs ADD COLUMN employee_name VARCHAR(255) NULL AFTER employee_id");
        }
    }
};
