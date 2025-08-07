<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovalFlowRuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks (optional, but helpful if there are constraints)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('approval_flow_rules')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = Carbon::now();

        // Insert fresh data
        DB::table('approval_flow_rules')->insert([
            [
                'id' => 1,
                'department_id' => 10,
                'branch' => 'KARAWANG',
                'is_design' => 0,
                'approval_flow_id' => 4,
                'priority' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'department_id' => 10,
                'branch' => null,
                'is_design' => 0,
                'approval_flow_id' => 2,
                'priority' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'department_id' => 15,
                'branch' => null,
                'is_design' => 0,
                'approval_flow_id' => 3,
                'priority' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'department_id' => 8,
                'branch' => 'KARAWANG',
                'is_design' => 0,
                'approval_flow_id' => 4,
                'priority' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'department_id' => 8,
                'branch' => null,
                'is_design' => 0,
                'approval_flow_id' => 3,
                'priority' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'department_id' => 18,
                'branch' => null,
                'is_design' => 0,
                'approval_flow_id' => 3,
                'priority' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'department_id' => 18,
                'branch' => 'KARAWANG',
                'is_design' => 0,
                'approval_flow_id' => 4,
                'priority' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
