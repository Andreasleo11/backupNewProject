<?php

namespace Database\Seeders;

use App\Infrastructure\Approval\Models\RuleStepTemplate;
use App\Infrastructure\Approval\Models\RuleTemplate;
use Illuminate\Database\Seeder;

class ApprovalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tpl = RuleTemplate::create([
            'model_type' => \App\Infrastructure\Persistence\Eloquent\Models\VerificationReport::class,
            'code' => 'VR-GENERAL',
            'name' => 'VR General Flow',
            'priority' => 50,
            'match_expr' => ['department' => 'FIN', 'amount_gt' => 100000000],
            'active' => true,
        ]);

        RuleStepTemplate::create([
            'rule_template_id' => $tpl->id, 'sequence' => 1, 'approver_type' => 'role', 'approver_id' => 2, // e.g. FINANCE MANAGER role_id
        ]);

        RuleStepTemplate::create([
            'rule_template_id' => $tpl->id, 'sequence' => 2, 'approver_type' => 'role', 'approver_id' => 3, 'final' => true, // e.g. DIRECTOR
        ]);
    }
}
