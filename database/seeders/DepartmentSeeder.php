<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Department::truncate();

        $departments = [
            ['dept_no' => '341', 'name' => 'QA', 'code' => 'QA', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '340', 'name' => 'QC', 'code' => 'QC', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '100', 'name' => 'ACCOUNTING', 'code' => 'ACU', 'branch' => null, 'is_office' => true, 'is_active' => true],
            ['dept_no' => '000', 'name' => 'MANAGEMENT', 'code' => 'ADM', 'branch' => null, 'is_office' => true, 'is_active' => true],
            ['dept_no' => '320', 'name' => 'PURCHASING', 'code' => 'PUR', 'branch' => null, 'is_office' => true, 'is_active' => true],
            ['dept_no' => '200', 'name' => 'BUSINESS', 'code' => 'BUS', 'branch' => null, 'is_office' => true, 'is_active' => true],
            ['dept_no' => '500', 'name' => 'PE', 'code' => 'PE', 'branch' => null, 'is_office' => true, 'is_active' => true],
            [
                'dept_no' => '390',
                'name' => 'PLASTIC INJECTION',
                'code' => 'PI',
                'branch' => null,
                'is_office' => false,
                'is_active' => true,
            ],
            ['dept_no' => '600', 'name' => 'COMPUTER', 'code' => 'CP', 'branch' => null, 'is_office' => true, 'is_active' => true],
            ['dept_no' => '363', 'name' => 'MOULDING', 'code' => 'MLD', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '330', 'name' => 'STORE', 'code' => 'STR', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '350', 'name' => 'MAINTENANCE', 'code' => 'MT', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '361', 'name' => 'SECOND PROCESS', 'code' => 'SPC', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '362', 'name' => 'ASSEMBLY', 'code' => 'ASM', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '311', 'name' => 'PPIC', 'code' => 'PIC', 'branch' => null, 'is_office' => false, 'is_active' => true],
            ['dept_no' => '310', 'name' => 'PERSONALIA', 'code' => 'HRD', 'branch' => null, 'is_office' => true, 'is_active' => true],
            ['dept_no' => '331', 'name' => 'LOGISTIC', 'code' => 'LOG', 'branch' => null, 'is_office' => false, 'is_active' => true],
            [
                'dept_no' => '351',
                'name' => 'MAINTENANCE MACHINE',
                'code' => 'MTM',
                'branch' => null,
                'is_office' => false,
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(['code' => $department['code']], $department);
        }
    }
}
