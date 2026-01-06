<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Allow NULL on start_date / end_date first
        Schema::table('employees', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });

        // Clean legacy zero-dates
        DB::table('employees')
            ->where('start_date', '0000-00-00')
            ->update(['start_date' => null]);

        DB::table('employees')
            ->where('end_date', '0000-00-00')
            ->update(['end_date' => null]);

        DB::table('employees')
            ->where('created_at', '0000-00-00 00:00:00')
            ->update(['created_at' => null]);

        DB::table('employees')
            ->where('updated_at', '0000-00-00 00:00:00')
            ->update(['updated_at' => null]);

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'NIK') && ! Schema::hasColumn('employees', 'nik')) {
                $table->renameColumn('NIK', 'nik');
            }
            if (Schema::hasColumn('employees', 'Nama') && ! Schema::hasColumn('employees', 'name')) {
                $table->renameColumn('Nama', 'name');
            }
            if (Schema::hasColumn('employees', 'Gender') && ! Schema::hasColumn('employees', 'gender')) {
                $table->renameColumn('Gender', 'gender');
            }
            if (Schema::hasColumn('employees', 'Dept') && ! Schema::hasColumn('employees', 'dept_code')) {
                $table->renameColumn('Dept', 'dept_code');
            }
            if (Schema::hasColumn('employees', 'jabatan') && ! Schema::hasColumn('employees', 'position')) {
                $table->renameColumn('jabatan', 'position');
            }
            if (Schema::hasColumn('employees', 'Branch') && ! Schema::hasColumn('employees', 'branch')) {
                $table->renameColumn('Branch', 'branch');
            }
            if (Schema::hasColumn('employees', 'employee_status') && ! Schema::hasColumn('employees', 'employment_type')) {
                $table->renameColumn('employee_status', 'employment_type');
            }
            if (Schema::hasColumn('employees', 'status') && ! Schema::hasColumn('employees', 'employment_scheme')) {
                $table->renameColumn('status', 'employment_scheme');
            }
            if (Schema::hasColumn('employees', 'Grade') && ! Schema::hasColumn('employees', 'grade_code')) {
                $table->renameColumn('Grade', 'grade_code');
            }
            if (Schema::hasColumn('employees', 'level') && ! Schema::hasColumn('employees', 'grade_level')) {
                $table->renameColumn('level', 'grade_level');
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            // adjust types
            $table->char('nik', 5)->change();
            $table->char('gender', 1)->change(); // enforce via app validation
            $table->char('dept_code', 3)->change();
            $table->string('position', 255)->nullable()->change();
            $table->string('branch', 20)->change();
            $table->string('employment_type', 50)->change();
            $table->string('employment_scheme', 100)->change();
            $table->string('grade_code', 10)->change();
            $table->integer('grade_level')->nullable()->change();
            $table->integer('jatah_cuti_tahun')->default(0)->change();
            $table->char('organization_structure', 6)->nullable()->change();
        });

        // Add indexes only if they don't exist
        $sm = Schema::getConnection()->getDoctrineSchemaManager();
        $indexesFound = $sm->listTableIndexes('employees');

        Schema::table('employees', function (Blueprint $table) use ($indexesFound) {
            // Check and add unique index for nik
            if (! isset($indexesFound['employees_nik_unique'])) {
                $table->unique('nik');
            }

            // Check and add regular indexes
            if (! isset($indexesFound['employees_dept_code_index'])) {
                $table->index('dept_code');
            }
            if (! isset($indexesFound['employees_employment_type_index'])) {
                $table->index('employment_type');
            }
            if (! isset($indexesFound['employees_branch_index'])) {
                $table->index('branch');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop indexes that were added in up()
        Schema::table('employees', function (Blueprint $table) {
            // Laravel will infer index names from columns
            $table->dropUnique(['nik']);            // employees_nik_unique
            $table->dropIndex(['dept_code']);       // employees_dept_code_index
            $table->dropIndex(['employment_type']); // employees_employment_type_index
            $table->dropIndex(['branch']);          // employees_branch_index
        });

        // 2. Change types back to original definitions
        // (based on your current table structure before refactor)
        Schema::table('employees', function (Blueprint $table) {
            // originally: varchar(255)
            $table->string('nik', 255)->change();
            $table->string('gender', 255)->change();
            $table->string('dept_code', 255)->change();
            $table->string('position', 255)->nullable()->change(); // jabatan was nullable
            $table->string('branch', 255)->change();
            $table->string('employment_type', 255)->change();
            $table->string('employment_scheme', 255)->change();
            $table->string('grade_code', 255)->change();

            // originally: int(11) nullable
            $table->integer('grade_level')->nullable()->change();

            // originally: int(11) not null default 0
            $table->integer('jatah_cuti_tahun')->default(0)->change();

            // originally: varchar(255) nullable
            $table->string('organization_structure', 255)->nullable()->change();
        });

        // 3. Rename columns back to original names
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('nik', 'NIK');
            $table->renameColumn('name', 'Nama');
            $table->renameColumn('gender', 'Gender');
            $table->renameColumn('dept_code', 'Dept');
            $table->renameColumn('position', 'jabatan');
            $table->renameColumn('branch', 'Branch');
            $table->renameColumn('employment_type', 'employee_status');
            $table->renameColumn('employment_scheme', 'status');
            $table->renameColumn('grade_code', 'Grade');
            $table->renameColumn('grade_level', 'level');
        });
    }
};
