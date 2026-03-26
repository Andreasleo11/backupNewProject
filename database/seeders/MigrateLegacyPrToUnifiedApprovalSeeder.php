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

        DB::transaction(function () use ($legacyPrs, $defaultRule) {
            foreach ($legacyPrs as $pr) {
                // Determine Status
                $mappedStatus = match ((int) $pr->status) {
                    4 => 'APPROVED',
                    5 => 'REJECTED',
                    3 => 'IN_REVIEW',
                    8 => 'DRAFT',
                    default => 'DRAFT',
                };
                
                // If Cancelled
                if ((int) $pr->is_cancel === 1) {
                    $mappedStatus = 'CANCELED';
                }

                /** @var ApprovalRequest $approvalRequest */
                $approvalRequest = $pr->approvalRequest()->create([
                    'status'          => $mappedStatus,
                    'rule_template_id'=> $defaultRule->id,
                    'current_step'    => 1, // Will update based on max step later
                    'submitted_by'    => $pr->user_id_create,
                    'submitted_at'    => $pr->created_at,
                    'meta'            => [],
                ]);

                // Migrate Autographs
                $maxSequence = 1;
                $labels = [
                    1 => 'Created By', 2 => 'Checked By', 3 => 'Known By', 4 => 'Approved By',
                    5 => 'Approved By', 6 => 'Approved By', 7 => 'Approved By',
                ];

                for ($i = 1; $i <= 7; $i++) {
                    $col = "autograph_{$i}";
                    $userCol = "autograph_user_{$i}";

                    if (!empty($pr->$col)) {
                        $userName = is_string($pr->$userCol) ? $pr->$userCol : 'Unknown';
                        
                        ApprovalStep::create([
                            'approval_request_id'         => $approvalRequest->id,
                            'sequence'                    => $i,
                            'approver_type'               => 'user',
                            'approver_id'                 => 0, // Legacy fallback id
                            'approver_snapshot_name'      => $userName,
                            'approver_snapshot_role_slug' => null,
                            'approver_snapshot_label'     => $labels[$i] ?? "Approver {$i}",
                            'status'                      => 'APPROVED',
                            'acted_by'                    => 0, // Legacy fallback id
                            'acted_at'                    => $pr->updated_at,
                            'remarks'                     => 'Migrated from legacy system',
                            'signature_image_path'        => $pr->$col,
                        ]);
                        $maxSequence = $i;
                    }
                }
                
                // Set the correct step pointer
                if ($mappedStatus === 'APPROVED') {
                    $approvalRequest->update(['current_step' => $maxSequence + 1]);
                } else {
                    $approvalRequest->update(['current_step' => $maxSequence]);
                }
            }
        });

        $this->command->info("Migrated {$legacyPrs->count()} legacy Purchase Requests to unified approval system.");
    }
}
