<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

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

        Department::truncate();

        Department::create([
            'dept_no' => '341',
            'name' => 'QA',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '340',
            'name' => 'QC',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '100',
            'name' => 'ACCOUNTING',
            'is_office' => true,
        ]);

        Department::create([
            'dept_no' => '000',
            'name' => 'DIRECTOR',
            'is_office' => true,
        ]);

        Department::create([
            'dept_no' => '320',
            'name' => 'PURCHASING',
            'is_office' => true,
        ]);

        Department::create([
            'dept_no' => null,
            'name' => 'HRD',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '200',
            'name' => 'BUSINESS',
            'is_office' => true,
        ]);

        Department::create([
            'dept_no' => '500',
            'name' => 'PE',
            'is_office' => true,
        ]);

        Department::create([
            'dept_no' => '390',
            'name' => 'PLASTIC INJECTION',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '600',
            'name' => 'COMPUTER',
            'is_office' => true,
        ]);

        Department::create([
            'dept_no' => '363',
            'name' => 'MOULDING',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '330',
            'name' => 'STORE',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '350',
            'name' => 'MAINTENANCE',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '361',
            'name' => 'SECOND PROCESS',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '362',
            'name' => 'ASSEMBLY',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '311',
            'name' => 'PPIC',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '310',
            'name' => 'PERSONALIA',
            'is_office' => true,
        ]);

        Department::create([
            'dept_no' => null,
            'name' => 'MANAGEMENT',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '331',
            'name' => 'LOGISTIC',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => '351',
            'name' => 'MAINTENANCE MOULDING',
            'is_office' => false,
        ]);

        Department::create([
            'dept_no' => null,
            'name' => 'MAINTENANCE UTILITY',
            'is_office' => false,
        ]);
    }
}
