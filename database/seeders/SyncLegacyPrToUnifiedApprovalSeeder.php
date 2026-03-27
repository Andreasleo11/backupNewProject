<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Models\PurchaseRequest;
use App\Application\PurchaseRequest\Services\PurchaseRequestContextBuilder;
use App\Infrastructure\Approval\Services\DefaultRuleResolver;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class SyncLegacyPrToUnifiedApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Cleanup existing PR approval data to allow re-migration
        $this->command->info('Cleaning up existing Purchase Request approval data...');
        
        ApprovalStep::whereHas('request', function ($query) {
            $query->where('approvable_type', PurchaseRequest::class);
        })->delete();

        ApprovalRequest::where('approvable_type', PurchaseRequest::class)->delete();

        $legacyPrs = PurchaseRequest::all();
        if ($legacyPrs->isEmpty()) {
            $this->command->info('No Purchase Requests found to migrate.');
            return;
        }

        $contextBuilder = new PurchaseRequestContextBuilder();
        $resolver = new DefaultRuleResolver();

        $legacyToModernRole = [
            'DEPT_HEAD'   => 'department-head',
            'GM'          => 'general-manager',
            'VERIFICATOR' => 'verificator',
            'PURCHASER'   => 'purchaser',
            'DIRECTOR'    => 'director',
            'HEAD_DESIGN' => 'department-head',
        ];

        DB::transaction(function () use ($legacyPrs, $contextBuilder, $resolver, $legacyToModernRole) {
            foreach ($legacyPrs as $pr) {
                // 1. Resolve Rule Template
                $context = $contextBuilder->build($pr);
                $rule = $resolver->resolveFor(PurchaseRequest::class, $context);
                
                if (!$rule) {
                    $this->command->warn("No rule found for PR #{$pr->id} ({$pr->pr_no}). Skipping status backfill, using default.");
                    continue;
                }

                // 2. Fetch existing legacy signatures
                $legacySignatures = DB::table('purchase_request_signatures')
                    ->where('purchase_request_id', $pr->id)
                    ->get()
                    ->keyBy('step_code');

                // 3. Create Approval Request
                $submittedBy = DB::table('users')->where('id', $pr->user_id_create)->exists() ? $pr->user_id_create : null;
                
                $approvalRequest = $pr->approvalRequest()->create([
                    'status'           => 'DRAFT', // will update once steps are set
                    'rule_template_id' => $rule->id,
                    'current_step'     => 1,
                    'submitted_by'     => $submittedBy,
                    'submitted_at'     => $pr->created_at,
                    'meta'             => ['migrated_from_v2_recovery' => true],
                ]);

                // 4. Create ALL steps from rule template
                $templateSteps = $rule->steps()->orderBy('sequence')->get();
                $allApproved = true;
                $anyApproved = false;
                $currentStepSequence = 1;
                $foundCurrent = false;
                $roleUsageCounter = [];

                foreach ($templateSteps as $tStep) {
                    $roleName = Role::find($tStep->approver_id)?->name;
                    $roleUsageCounter[$roleName] = ($roleUsageCounter[$roleName] ?? 0) + 1;

                    $legacyCode = array_search($roleName, $legacyToModernRole);
                    
                    // Specific override for duplicate roles like department-head
                    if ($roleName === 'department-head' && $roleUsageCounter[$roleName] === 2) {
                        $legacyCode = 'HEAD_DESIGN';
                    }

                    $sig = $legacySignatures->get($legacyCode);
                    $status = 'PENDING';
                    $actedBy = null;
                    $actedAt = null;
                    $imagePath = null;
                    $sha256 = null;
                    $sigId = null;

                    if ($sig) {
                        $status = 'APPROVED';
                        $anyApproved = true;
                        $actedBy = DB::table('users')->where('id', $sig->signed_by_user_id)->exists() ? $sig->signed_by_user_id : null;
                        $actedAt = $sig->signed_at ?: ($sig->created_at ?: $pr->created_at);
                        $imagePath = $sig->image_path;
                        
                        // Try to link user_signature_id
                        if ($actedBy) {
                            $userSig = \App\Infrastructure\Persistence\Eloquent\Models\UserSignature::where('user_id', $actedBy)
                                ->whereNull('revoked_at')
                                ->orderByDesc('is_default')
                                ->first();
                            if ($userSig) {
                                $sigId = $userSig->id;
                                $sha256 = $userSig->sha256;
                            }
                        }
                    } else {
                        $allApproved = false;
                        if (!$foundCurrent) {
                            $currentStepSequence = $tStep->sequence;
                            $foundCurrent = true;
                        }
                    }

                    ApprovalStep::create([
                        'approval_request_id'         => $approvalRequest->id,
                        'sequence'                    => $tStep->sequence,
                        'approver_type'               => 'role',
                        'approver_id'                 => $tStep->approver_id,
                        'approver_snapshot_name'      => $imagePath ? pathinfo($imagePath, PATHINFO_FILENAME) : null,
                        'approver_snapshot_role_slug' => $roleName,
                        'approver_snapshot_label'     => $roleName ? ucwords(str_replace('-', ' ', $roleName)) : 'Approver',
                        'status'                      => $status,
                        'acted_by'                    => $actedBy,
                        'acted_at'                    => $actedAt,
                        'signature_image_path'        => $imagePath,
                        'signature_sha256'            => $sha256,
                        'user_signature_id'           => $sigId,
                        'remarks'                     => $sig ? 'Migrated from legacy signatures table' : null,
                    ]);
                }

                // 5. Finalize status
                $finalStatus = 'DRAFT';
                if ($allApproved && $templateSteps->isNotEmpty()) {
                    $finalStatus = 'APPROVED';
                    $currentStepSequence = $templateSteps->last()->sequence + 1;
                } elseif ($anyApproved || $legacySignatures->has('MAKER')) {
                    $finalStatus = 'IN_REVIEW';
                }

                if ((int) $pr->is_cancel === 1) {
                    $finalStatus = 'CANCELED';
                }

                $approvalRequest->update([
                    'status' => $finalStatus,
                    'current_step' => $currentStepSequence,
                ]);
            }
        });

        $this->command->info("Recovered and migrated {$legacyPrs->count()} Purchase Requests with full rule-template synchronization.");
    }
}
