<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add evaluation_type and approval_status columns to evaluation_datas.
     *
     * evaluation_type  → 'regular' | 'yayasan' | 'magang'
     *   Backfill: read employment_scheme from the joined employees table.
     *   Falls back to 'regular' if the employee row is missing.
     *
     * approval_status  → 'pending' | 'graded' | 'dept_approved' | 'fully_approved' | 'rejected'
     *   Backfill: derived from existing depthead / generalmanager / pengawas string values.
     *
     * NOTE: The old depthead / generalmanager columns are NOT dropped here.
     *       They remain for rollback safety and will be removed in a future migration
     *       once the new status column is verified in production.
     */
    public function up(): void
    {
        Schema::table('evaluation_datas', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluation_datas', 'evaluation_type')) {
                $table->string('evaluation_type')->default('regular')->after('dept')
                    ->comment('regular | yayasan | magang');
            }

            if (! Schema::hasColumn('evaluation_datas', 'approval_status')) {
                $table->string('approval_status')->default('pending')->after('evaluation_type')
                    ->comment('pending | graded | dept_approved | fully_approved | rejected');
            }

            if (! Schema::hasIndex('evaluation_datas', 'idx_eval_type')) {
                $table->index('evaluation_type', 'idx_eval_type');
            }

            if (! Schema::hasIndex('evaluation_datas', 'idx_approval_status')) {
                $table->index('approval_status', 'idx_approval_status');
            }
        });

        // ── Backfill evaluation_type ─────────────────────────────────────────
        // Join with employees (karyawans) table to read employment_scheme.
        // Using raw DB update for performance on potentially large datasets.
        $employeeTable = DB::getTablePrefix() . 'employees';

        DB::statement("
            UPDATE evaluation_datas ed
            LEFT JOIN {$employeeTable} k ON k.nik = ed.NIK
            SET ed.evaluation_type = CASE
                WHEN k.employment_scheme LIKE '%YAYASAN%' THEN 'yayasan'
                WHEN k.employment_scheme LIKE '%MAGANG%'  THEN 'magang'
                ELSE 'regular'
            END
        ");

        // ── Backfill approval_status ─────────────────────────────────────────
        // Priority (highest → lowest):
        //   rejected:       depthead = 'rejected' OR generalmanager = 'rejected'
        //   fully_approved: generalmanager IS NOT NULL and != 'rejected'
        //   dept_approved:  depthead IS NOT NULL and != 'rejected'
        //   graded:         pengawas IS NOT NULL and != ''
        //   pending:        everything else
        DB::statement("
            UPDATE evaluation_datas
            SET approval_status = CASE
                WHEN depthead = 'rejected' OR generalmanager = 'rejected'
                    THEN 'rejected'
                WHEN generalmanager IS NOT NULL AND generalmanager != '' AND generalmanager != 'rejected'
                    THEN 'fully_approved'
                WHEN depthead IS NOT NULL AND depthead != '' AND depthead != 'rejected'
                    THEN 'dept_approved'
                WHEN pengawas IS NOT NULL AND pengawas != ''
                    THEN 'graded'
                ELSE 'pending'
            END
        ");
    }

    /**
     * Reverse — drop the two new columns and their indexes.
     * The old depthead/generalmanager columns were never touched, so rollback is clean.
     */
    public function down(): void
    {
        Schema::table('evaluation_datas', function (Blueprint $table) {
            $table->dropIndex('idx_eval_type');
            $table->dropIndex('idx_approval_status');
            $table->dropColumn(['evaluation_type', 'approval_status']);
        });
    }
};
