<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PoWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding PO Approval Workflow...');

        $modelType = \App\Models\PurchaseOrder::class;

        // Check if baseline PO director rule already exists
        $existingRule = RuleTemplate::where('model_type', $modelType)
            ->where('code', 'po.baseline.director')
            ->first();

        if ($existingRule) {
            $this->command->warn("⚠️  PO baseline director approval rule already exists (ID: {$existingRule->id})");
            $this->command->info('ℹ️  Skipping seeder - workflow already configured');
            return;
        }

        // Get director role
        $directorRole = Role::where('name', 'director')
            ->where('guard_name', config('auth.defaults.guard', 'web'))
            ->first();

        if (!$directorRole) {
            $this->command->error('❌ Director role not found. Run roles and permissions seeders first.');
            return;
        }

        DB::transaction(function () use ($modelType, $directorRole) {
            // Create the baseline rule for PO director approval
            $rule = RuleTemplate::create([
                'model_type' => $modelType,
                'code' => 'po.baseline.director',
                'name' => 'PO Baseline Director Approval',
                'active' => true,
                'priority' => 100, // High priority for baseline rule
                'match_expr' => [], // Empty match - applies to all POs
            ]);

            // Create single step: director approval
            $rule->steps()->create([
                'sequence' => 1,
                'approver_type' => 'role',
                'approver_id' => $directorRole->id,
                'final' => true, // Director approval is final
                'parallel_group' => 0,
            ]);

            $this->command->info("✅ Created PO baseline director approval rule (ID: {$rule->id})");
            $this->command->info("   - Model: {$modelType}");
            $this->command->info("   - Code: po.baseline.director");
            $this->command->info("   - Approver: Director role (ID: {$directorRole->id})");
            $this->command->info("   - Workflow: Single-step director approval");
        });

        $this->command->info('🎉 PO Approval Workflow seeding completed successfully!');
    }
}