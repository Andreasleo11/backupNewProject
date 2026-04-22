<?php

namespace Database\Seeders;

use App\Application\Overtime\Services\OvertimeApprovalContextBuilder;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\UserSignature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SyncLegacyOvertimeToUnifiedApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning up existing Overtime approval data for migration...');

        // 0. Cleanup only those that we are migrating (OvertimeForm)
        ApprovalStep::whereHas('request', function ($query) {
            $query->where('approvable_type', OvertimeForm::class);
        })->delete();

        ApprovalRequest::where('approvable_type', OvertimeForm::class)->delete();

        $legacyForms = OvertimeForm::all();
        if ($legacyForms->isEmpty()) {
            $this->command->info('No Overtime forms found to migrate.');

            return;
        }

        $contextBuilder = new OvertimeApprovalContextBuilder;
        $resolver = app(\App\Domain\Approval\Contracts\RuleResolver::class);

        // Legacy role_slug to Modern role mapping
        $legacyToModernRole = [
            'department-head' => 'department-head',
            'general-manager' => 'general-manager',
            'verificator' => 'verificator',
            'dept_head' => 'department-head',
            'DEPT_HEAD' => 'department-head',
            'gm' => 'general-manager',
            'GM' => 'general-manager',
            'director' => 'director',
            'DIRECTOR' => 'director',
            'supervisor' => 'department-head', // Legacy supervisor maps to dept-head
        ];

        DB::transaction(function () use ($legacyForms, $contextBuilder, $resolver, $legacyToModernRole) {
            $count = 0;
            foreach ($legacyForms as $form) {
                // 1. Resolve Rule Template based on current Form context
                $context = $contextBuilder->build($form);
                $rule = $resolver->resolveFor(OvertimeForm::class, $context);

                if (! $rule) {
                    $this->command->warn("No rule template found for Overtime #{$form->id}. Skipping.");
                    continue;
                }

                // 2. Fetch existing legacy signatures from 'overtime_form_approvals'
                // We join with approval_flow_steps to get the role_slug for mapping
                $legacyApprovals = DB::table('overtime_form_approvals')
                    ->join('approval_flow_steps', 'overtime_form_approvals.flow_step_id', '=', 'approval_flow_steps.id')
                    ->where('overtime_form_approvals.overtime_form_id', $form->id)
                    ->select('overtime_form_approvals.*', 'approval_flow_steps.role_slug')
                    ->get()
                    ->keyBy('role_slug');

                // 3. Create Unified Approval Request
                $approvalRequest = $form->approvalRequest()->create([
                    'status' => 'DRAFT', // Temporary
                    'rule_template_id' => $rule->id,
                    'current_step' => 1,
                    'submitted_by' => $form->user_id,
                    'submitted_at' => $form->created_at,
                    'meta' => ['migrated_from_legacy_overtime' => true],
                ]);

                // 4. Create all steps from the resolved Rule Template
                $templateSteps = $rule->steps()->orderBy('sequence')->get();
                $allApproved = true;
                $anyApproved = false;
                $currentStepSequence = 1;
                $foundCurrent = false;

                foreach ($templateSteps as $tStep) {
                    $role = Role::find($tStep->approver_id);
                    $roleName = $role?->name;

                    // Try to find the matching legacy signature
                    // We check ALL legacy codes that map to our modern roleName
                    $legacyCodes = array_keys($legacyToModernRole, $roleName);
                    if (! in_array($roleName, $legacyCodes)) {
                        $legacyCodes[] = $roleName;
                    }

                    $sig = null;
                    foreach ($legacyCodes as $code) {
                        if ($legacyApprovals->has($code)) {
                            $sig = $legacyApprovals->get($code);
                            break;
                        }
                    }

                    $status = 'PENDING';
                    $actedBy = null;
                    $actedAt = null;
                    $imagePath = null;
                    $sha256 = null;
                    $sigId = null;

                    if ($sig && $sig->status === 'approved') {
                        $status = 'APPROVED';
                        $anyApproved = true;
                        $actedBy = $sig->approver_id;
                        $actedAt = $sig->signed_at ?: $sig->updated_at;
                        $imagePath = $sig->signature_path;

                        // Link to UserSignature if exists
                        if ($actedBy) {
                            $userSig = UserSignature::where('user_id', $actedBy)
                                ->whereNull('revoked_at')
                                ->orderByDesc('is_default')
                                ->first();
                            if ($userSig) {
                                $sigId = $userSig->id;
                                $sha256 = $userSig->sha256;
                            }
                        }
                    } elseif ($sig && $sig->status === 'rejected') {
                        $status = 'REJECTED';
                        $allApproved = false;
                        $actedBy = $sig->approver_id;
                        $actedAt = $sig->signed_at ?: $sig->updated_at;
                        if (! $foundCurrent) {
                            $currentStepSequence = $tStep->sequence;
                            $foundCurrent = true;
                        }
                    } else {
                        $allApproved = false;
                        if (! $foundCurrent) {
                            $currentStepSequence = $tStep->sequence;
                            $foundCurrent = true;
                        }
                    }

                    ApprovalStep::create([
                        'approval_request_id' => $approvalRequest->id,
                        'sequence' => $tStep->sequence,
                        'approver_type' => 'role',
                        'approver_id' => $tStep->approver_id,
                        'approver_snapshot_name' => $roleName ? ucwords(str_replace('-', ' ', $roleName)) : 'Approver',
                        'approver_snapshot_role_slug' => $roleName,
                        'approver_snapshot_label' => $roleName ? ucwords(str_replace('-', ' ', $roleName)) : 'Approver',
                        'status' => $status,
                        'acted_by' => $actedBy,
                        'acted_at' => $actedAt,
                        'signature_image_path' => $imagePath,
                        'signature_sha256' => $sha256,
                        'user_signature_id' => $sigId,
                        'remarks' => $sig ? ($sig->comment ?? 'Migrated from legacy overtime signature') : null,
                    ]);
                }

                // 5. Update overall status
                $finalStatus = 'IN_REVIEW'; // Default for legacy in-progress forms
                if ($allApproved && $templateSteps->isNotEmpty()) {
                    $finalStatus = 'APPROVED';
                    $currentStepSequence = $templateSteps->last()->sequence + 1;
                }

                if ($form->status === 'rejected') {
                    $finalStatus = 'REJECTED';
                } elseif ($form->status === 'approved') {
                    $finalStatus = 'APPROVED';
                }

                $approvalRequest->update([
                    'status' => $finalStatus,
                    'current_step' => $currentStepSequence,
                ]);

                $count++;
            }
            $this->command->info("Migrated {$count} Overtime forms successfully.");
        });
    }
}
