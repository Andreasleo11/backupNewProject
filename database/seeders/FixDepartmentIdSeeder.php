<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FixDepartmentIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the old department ID to new department ID mapping
        $deptMapping = [
            8 => 6,
            9 => 7,
            11 => 8,
            15 => 9,
            16 => 10,
            17 => 11,
            18 => 12,
            19 => 13,
            20 => 14,
            21 => 15,
            22 => 16,
            24 => 17,
            25 => 18, // Maintenance Moulding -> Maintenance Machine
        ];

        // Update users based on the new department ID mapping
        foreach ($deptMapping as $oldDeptId => $newDeptId) {
            \App\Models\User::where('department_id', $oldDeptId)->update([
                'department_id' => $newDeptId,
            ]);
        }
    }
}
