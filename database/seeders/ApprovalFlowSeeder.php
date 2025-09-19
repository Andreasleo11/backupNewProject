<?php

namespace Database\Seeders;

use App\Models\ApprovalFlow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApprovalFlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $flows = [
            [
                "slug" => "dept-head-director",
                "name" => "Creator → Dept Head → Director",
                "steps" => [
                    ["step_order" => 1, "role_slug" => "creator", "mandatory" => true],
                    ["step_order" => 2, "role_slug" => "dept_head", "mandatory" => true],
                    ["step_order" => 3, "role_slug" => "director", "mandatory" => true],
                ],
            ],
            [
                "slug" => "supervisor-dept-head-director",
                "name" => "Creator → Supervisor → Dept Head → Director",
                "steps" => [
                    ["step_order" => 1, "role_slug" => "creator", "mandatory" => true],
                    ["step_order" => 2, "role_slug" => "supervisor", "mandatory" => true],
                    ["step_order" => 3, "role_slug" => "dept_head", "mandatory" => true],
                    ["step_order" => 4, "role_slug" => "director", "mandatory" => true],
                ],
            ],
            [
                "slug" => "dept-head-gm-director",
                "name" => "Creator → Dept Head → GM → Director",
                "steps" => [
                    ["step_order" => 1, "role_slug" => "creator", "mandatory" => true],
                    ["step_order" => 2, "role_slug" => "dept_head", "mandatory" => true],
                    ["step_order" => 3, "role_slug" => "gm", "mandatory" => true],
                    ["step_order" => 4, "role_slug" => "director", "mandatory" => true],
                ],
            ],
            [
                "slug" => "gm-director",
                "name" => "Creator → GM → Director",
                "steps" => [
                    ["step_order" => 1, "role_slug" => "creator", "mandatory" => true],
                    ["step_order" => 2, "role_slug" => "gm", "mandatory" => true],
                    ["step_order" => 3, "role_slug" => "director", "mandatory" => true],
                ],
            ],
        ];

        /**
         * Run once in a seeder (e.g. ApprovalFlowSeeder) or an artisan command.
         */
        foreach ($flows as $flow) {
            // 1. Upsert the flow header
            $approvalFlow = ApprovalFlow::updateOrCreate(
                ["slug" => $flow["slug"]],
                [
                    "name" => $flow["name"],
                    "created_by" => 5, // ← adjust to your sys-admin user ID
                ],
            );

            // 2. Replace its steps to keep them in sync
            $approvalFlow->steps()->delete(); // wipe old steps if the flow already existed

            foreach ($flow["steps"] as $step) {
                $approvalFlow->steps()->create($step);
            }
        }
    }
}
