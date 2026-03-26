<?php

namespace Database\Seeders;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\ApprovalFlowStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateLegacyOvertimeToUnifiedApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Find forms that have entries in legacy overtime_form_approvals but no unified approvalRequest
        $legacyForms = OvertimeForm::whereDoesntHave('approvalRequest')
            ->whereIn('id', function($query) {
                $query->select('overtime_form_id')->from('overtime_form_approvals');
            })
            ->get();

        if ($legacyForms->isEmpty()) {
            if ($this->command) $this->command->info('No legacy Overtime forms found to migrate.');
            return;
        }

        // 2. Resolve default rule template for Overtime
        $defaultRule = RuleTemplate::where('model_type', OvertimeForm::class)
            ->where('code', 'ot.default')
            ->first();

        if (!$defaultRule) {
            if ($this->command) $this->command->error('No RuleTemplate found for OvertimeForm. Run OvertimeApprovalRulesSeeder first.');
            return;
        }

        DB::transaction(function () use ($legacyForms, $defaultRule) {
            foreach ($legacyForms as $form) {
                // Determine form-level status
                $mappedStatus = match ($form->status) {
                    'approved' => 'APPROVED',
                    'rejected' => 'REJECTED',
                    default    => 'IN_REVIEW',
                };

                // Create unified ApprovalRequest
                /** @var ApprovalRequest $req */
                $req = $form->approvalRequest()->create([
                    'status'           => $mappedStatus,
                    'rule_template_id' => $defaultRule->id,
                    'current_step'     => 1, // Will update based on approvals
                    'submitted_by'     => $form->user_id,
                    'submitted_at'     => $form->created_at,
                    'meta'             => [
                        'migrated_from_legacy' => true,
                        'original_flow_id'     => $form->approval_flow_id,
                    ],
                ]);

                // Migrate legacy approvals from 'overtime_form_approvals'
                $legacyApprovals = DB::table('overtime_form_approvals')
                    ->where('overtime_form_id', $form->id)
                    ->orderBy('flow_step_id')
                    ->get();

                $maxApprovedSequence = 0;

                foreach ($legacyApprovals as $approval) {
                    // Find the sequence/label from legacy flow step
                    $flowStep = ApprovalFlowStep::find($approval->flow_step_id);
                    $sequence = $flowStep?->step_order ?? 1;
                    $roleSlug = $flowStep?->role_slug ?? 'approver';

                    $approver = User::find($approval->approver_id);

                    ApprovalStep::create([
                        'approval_request_id'         => $req->id,
                        'sequence'                    => $sequence,
                        'approver_type'               => 'role',
                        'approver_id'                 => 0, // snapshot based migration
                        'approver_snapshot_name'      => $approver?->name ?? 'Unknown',
                        'approver_snapshot_role_slug' => $roleSlug,
                        'approver_snapshot_label'     => ucwords(str_replace(['-', '_'], ' ', $roleSlug)),
                        'status'                      => strtoupper($approval->status),
                        'acted_by'                    => $approval->approver_id ?? 0,
                        'acted_at'                    => $approval->signed_at ?? $approval->updated_at,
                        'remarks'                     => $approval->comment ?? 'Migrated from legacy system',
                        'signature_image_path'        => $approval->signature_path,
                    ]);

                    if (strtoupper($approval->status) === 'APPROVED') {
                        $maxApprovedSequence = max($maxApprovedSequence, $sequence);
                    }
                }

                // Set correct current_step
                if ($mappedStatus === 'APPROVED') {
                    $req->update(['current_step' => $maxApprovedSequence + 1]);
                } else {
                    $req->update(['current_step' => $maxApprovedSequence ?: 1]);
                }
            }
        });

        if ($this->command) {
            $this->command->info("Migrated {$legacyForms->count()} legacy Overtime forms to unified approval system.");
        }
    }
}
