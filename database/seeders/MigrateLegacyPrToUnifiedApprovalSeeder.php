<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Models\PurchaseRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateLegacyPrToUnifiedApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $legacyPrs = PurchaseRequest::doesntHave('approvalRequest')->get();
        if ($legacyPrs->isEmpty()) {
            $this->command->info('No legacy Purchase Requests found to migrate.');
            return;
        }

        $defaultRule = RuleTemplate::where('model_type', PurchaseRequest::class)->first();
        if (!$defaultRule) {
            $this->command->error('No RuleTemplate found for PurchaseRequest. Run PrApprovalRulesSeeder first.');
            return;
        }

        $stepLabels = [
            'MAKER'       => 'Prepared By',
            'DEPT_HEAD'   => 'Checked By',
            'VERIFICATOR' => 'Checked By',
            'DIRECTOR'    => 'Approved By',
            'PURCHASER'   => 'Known By',
            'GM'          => 'Approved By',
            'HEAD_DESIGN' => 'Known By',
        ];

        $stepOrder = [
            'MAKER'       => 1,
            'DEPT_HEAD'   => 2,
            'VERIFICATOR' => 3,
            'DIRECTOR'    => 4,
            'PURCHASER'   => 5,
            'GM'          => 6,
            'HEAD_DESIGN' => 7,
        ];

        DB::transaction(function () use ($legacyPrs, $defaultRule, $stepLabels, $stepOrder) {
            foreach ($legacyPrs as $pr) {
                // Fetch signatures from the intermediate table created in Dec 2025
                $signatures = DB::table('purchase_request_signatures')
                    ->where('purchase_request_id', $pr->id)
                    ->orderBy('created_at')
                    ->get();
                
                // Determine Status from existing signatures
                $hasDirector = $signatures->contains('step_code', 'DIRECTOR');
                $hasGM = $signatures->contains('step_code', 'GM');
                $hasMaker = $signatures->contains('step_code', 'MAKER');

                $mappedStatus = 'DRAFT';
                if ($hasDirector || $hasGM) {
                    $mappedStatus = 'APPROVED';
                } elseif ($hasMaker || $signatures->isNotEmpty()) {
                    $mappedStatus = 'IN_REVIEW';
                }
                
                // Handle cancellation
                if ((int) $pr->is_cancel === 1) {
                    $mappedStatus = 'CANCELED';
                }

                // Validate submitted_by and signed_by to avoid FK constraint issues with deleted users
                $submittedBy = DB::table('users')->where('id', $pr->user_id_create)->exists() 
                    ? $pr->user_id_create 
                    : null;

                /** @var ApprovalRequest $approvalRequest */
                $approvalRequest = $pr->approvalRequest()->create([
                    'status'          => $mappedStatus,
                    'rule_template_id'=> $defaultRule->id,
                    'current_step'    => 1, 
                    'submitted_by'    => $submittedBy,
                    'submitted_at'    => $pr->created_at,
                    'meta'            => ['migrated_from_v2_recovery' => true],
                ]);

                $maxSequence = 0;
                foreach ($signatures as $sig) {
                    $sequence = $stepOrder[$sig->step_code] ?? ($maxSequence + 1);
                    
                    $actedBy = DB::table('users')->where('id', $sig->signed_by_user_id)->exists()
                        ? $sig->signed_by_user_id
                        : null;

                    $step = ApprovalStep::create([
                        'approval_request_id'         => $approvalRequest->id,
                        'sequence'                    => $sequence,
                        'approver_type'               => 'user',
                        'approver_id'                 => $actedBy,
                        'approver_snapshot_name'      => $this->getUserName($sig->signed_by_user_id),
                        'approver_snapshot_role_slug' => strtolower($sig->step_code),
                        'approver_snapshot_label'     => $stepLabels[$sig->step_code] ?? 'Approver',
                        'status'                      => 'APPROVED',
                        'acted_by'                    => $actedBy,
                        'acted_at'                    => $sig->signed_at ?: $sig->updated_at,
                        'remarks'                     => 'Migrated from legacy signatures table',
                        'signature_image_path'        => $sig->image_path,
                    ]);

                    // Try to enrich with modern signature link if the user has one
                    if ($actedBy) {
                        $userSig = \App\Infrastructure\Persistence\Eloquent\Models\UserSignature::where('user_id', $actedBy)
                            ->whereNull('revoked_at')
                            ->orderByDesc('is_default')
                            ->first();

                        if ($userSig) {
                            $step->update([
                                'user_signature_id' => $userSig->id,
                                'signature_sha256'  => $userSig->sha256,
                                // We keep the original image path from the legacy record 
                                // unless it's missing, to preserve history.
                            ]);
                        }
                    }
                    
                    $maxSequence = max($maxSequence, $sequence);
                }
                
                // Set the correct step pointer
                if ($mappedStatus === 'APPROVED') {
                    $approvalRequest->update(['current_step' => $maxSequence + 1]);
                } else {
                    $approvalRequest->update(['current_step' => $maxSequence ?: 1]);
                }
            }
        });

        $this->command->info("Recovered and migrated {$legacyPrs->count()} Purchase Requests.");
    }

    /**
     * Helper to get user name for snapshot
     */
    private function getUserName($userId)
    {
        if (!$userId) return 'Unknown';
        return DB::table('users')->where('id', $userId)->value('name') ?: 'Unknown';
    }
}
