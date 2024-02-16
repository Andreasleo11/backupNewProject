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

        Department::create(['name' => 'QA']);
        Department::create(['name' => 'QC']);
        Department::create(['name' => 'ACCOUNTING']);
        Department::create(['name' => 'DIRECTOR']);
        Department::create(['name' => 'PURCHASING']);
        Department::create(['name' => 'PRODUCTION']);
        Department::create(['name' => 'HRD']);
        Department::create(['name' => 'BUSINESS']);
        Department::create(['name' => 'PE']);
    }
}
