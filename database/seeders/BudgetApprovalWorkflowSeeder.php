<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\RuleStepTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetSummaryReport;
use Illuminate\Database\Seeder;

class BudgetApprovalWorkflowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Clear existing budget-related rules to avoid duplicates if re-run
        $modelTypes = [
            MonthlyBudgetReport::class,
            MonthlyBudgetSummaryReport::class,
        ];

        RuleTemplate::whereIn('model_type', $modelTypes)->each(function ($template) {
            $template->steps()->delete();
            $template->delete();
        });

        // 2. Define Workflows
        $this->seedBudgetRules(MonthlyBudgetReport::class);
        $this->seedBudgetRules(MonthlyBudgetSummaryReport::class);
    }

    private function seedBudgetRules(string $modelType): void
    {
        $shortName = str_replace('App\\Models\\', '', $modelType);

        if ($modelType === MonthlyBudgetReport::class) {
            // --- Departmental Reports (2 Steps: Dept Head -> GM/Director) ---

            // A. QA/QC Workflow (Director Approval)
            $qaqc = RuleTemplate::create([
                'model_type' => $modelType,
                'code' => 'budget-qaqc-' . strtolower($shortName),
                'name' => $shortName . ' Approval (QA/QC)',
                'priority' => 1,
                'active' => true,
                'match_expr' => [
                    'from_department_in' => ['QA', 'QC'],
                ],
            ]);

            $this->createSteps($qaqc, [
                ['sequence' => 1, 'role_id' => 70], // department-head
                ['sequence' => 2, 'role_id' => 52, 'final' => true], // director
            ]);

            // B. Moulding Workflow
            $moulding = RuleTemplate::create([
                'model_type' => $modelType,
                'code' => 'budget-moulding-' . strtolower($shortName),
                'name' => $shortName . ' Approval (Moulding)',
                'priority' => 2,
                'active' => true,
                'match_expr' => [
                    'is_moulding' => true,
                ],
            ]);

            $this->createSteps($moulding, [
                ['sequence' => 1, 'role_id' => 70], // department-head
                ['sequence' => 2, 'role_id' => 72, 'final' => true], // general-manager
            ]);

            // C. Standard Workflow (Catch-all)
            $standard = RuleTemplate::create([
                'model_type' => $modelType,
                'code' => 'budget-standard-' . strtolower($shortName),
                'name' => $shortName . ' Approval (Standard)',
                'priority' => 10,
                'active' => true,
                'match_expr' => [
                    'is_moulding' => false,
                ],
            ]);

            $this->createSteps($standard, [
                ['sequence' => 1, 'role_id' => 70], // department-head
                ['sequence' => 2, 'role_id' => 72, 'final' => true], // general-manager
            ]);
        } else {
            // --- Summary Reports ---

            // A. Moulding Summary (Dept Head -> Director)
            $mouldingSummary = RuleTemplate::create([
                'model_type' => $modelType,
                'code' => 'budget-summary-moulding-' . strtolower($shortName),
                'name' => $shortName . ' Approval (Moulding: DH -> Dir)',
                'priority' => 1,
                'active' => true,
                'match_expr' => [
                    'is_moulding' => true,
                ],
            ]);

            $this->createSteps($mouldingSummary, [
                ['sequence' => 1, 'role_id' => 70], // department-head (Moulding head)
                ['sequence' => 3, 'role_id' => 52, 'final' => true], // director
            ]);

            // B. Standard Summary (GM -> Director)
            $standardSummary = RuleTemplate::create([
                'model_type' => $modelType,
                'code' => 'budget-summary-standard-' . strtolower($shortName),
                'name' => $shortName . ' Approval (Standard: GM -> Dir)',
                'priority' => 10,
                'active' => true,
                'match_expr' => [
                    'is_moulding' => false,
                ],
            ]);

            $this->createSteps($standardSummary, [
                ['sequence' => 1, 'role_id' => 72], // general-manager
                ['sequence' => 2, 'role_id' => 52, 'final' => true], // director
            ]);
        }
    }

    private function createSteps(RuleTemplate $template, array $steps): void
    {
        foreach ($steps as $step) {
            RuleStepTemplate::create([
                'rule_template_id' => $template->id,
                'sequence' => $step['sequence'],
                'approver_type' => 'role',
                'approver_id' => $step['role_id'],
                'final' => $step['final'] ?? false,
            ]);
        }
    }
}
