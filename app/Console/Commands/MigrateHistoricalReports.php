<?php

namespace App\Console\Commands;

use App\Models\Report as LegacyReport;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationItem;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationItemDefect;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Domain\Verification\Enums\DefectSource;
use App\Domain\Verification\Enums\Severity;
use Spatie\Permission\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateHistoricalReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verification:migrate-historical
                            {--dry-run : Run the migration without saving changes to the database}
                            {--refresh : Wipe all existing verification reports and their approval records before migrating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate historical QA/QC reports to the new Verification system tables and create approval request/step records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $refresh = $this->option('refresh');

        if ($dryRun) {
            $this->comment('=== RUNNING IN DRY-RUN MODE (No changes will be saved) ===');
        }

        $legacyReports = LegacyReport::with(['details.defects.category'])->get();
        $total = $legacyReports->count();

        $this->info("Found {$total} legacy reports to migrate.");

        $migrated = 0;
        $skipped = 0;

        // Resolve rule template for VerificationReport
        $ruleTemplate = RuleTemplate::where('model_type', VerificationReport::class)
            ->where('code', 'verification.default')
            ->first();

        if (!$ruleTemplate && !$dryRun) {
            $this->error("Verification approval rule template ('verification.default') not found. Please run 'VerificationApprovalRulesSeeder' first.");
            return Command::FAILURE;
        }

        $leaderRole = Role::where('name', 'leader')->first();
        $deptHeadRole = Role::where('name', 'department-head')->first();

        try {
            DB::transaction(function () use ($legacyReports, $dryRun, $refresh, $ruleTemplate, $leaderRole, $deptHeadRole, &$migrated, &$skipped) {
                if ($refresh) {
                    $this->comment('Wiping existing verification reports and approval records...');
                    // Delete all new records
                    // Since it is inside the transaction, it is completely safe and will roll back in dry-run!
                    VerificationReport::query()->each(function (VerificationReport $report) {
                        // Delete related approval requests/steps
                        $report->approvalRequest()->each(function ($req) {
                            $req->steps()->delete();
                            $req->delete();
                        });
                        $report->items()->each(function ($item) {
                            $item->defects()->delete();
                            $item->delete();
                        });
                        $report->delete();
                    });
                }

                foreach ($legacyReports as $legacyReport) {
                    if (!$refresh) {
                        // Check if already migrated
                        $exists = VerificationReport::where('document_number', $legacyReport->doc_num)->exists();
                        if ($exists) {
                            $skipped++;
                            continue;
                        }
                    }

                    // Map status
                    $statusMap = [
                        0 => 'REJECTED',
                        1 => 'APPROVED',
                        2 => 'IN_REVIEW',
                    ];
                    $status = $statusMap[$legacyReport->is_approve] ?? 'DRAFT';

                    $meta = [
                        'legacy_id' => $legacyReport->id,
                        'autograph_1' => $legacyReport->autograph_1,
                        'autograph_2' => $legacyReport->autograph_2,
                        'autograph_3' => $legacyReport->autograph_3,
                        'autograph_user_1' => $legacyReport->autograph_user_1,
                        'autograph_user_2' => $legacyReport->autograph_user_2,
                        'autograph_user_3' => $legacyReport->autograph_user_3,
                        'attachment' => $legacyReport->attachment,
                        'description' => $legacyReport->description,
                        'has_been_emailed' => $legacyReport->has_been_emailed,
                    ];

                    $creatorId = \App\Infrastructure\Persistence\Eloquent\Models\User::where('name', $legacyReport->created_by)->first()?->id;

                    if (!$dryRun) {
                        $newReport = VerificationReport::create([
                            'document_number' => $legacyReport->doc_num,
                            'creator_id' => $creatorId ?? 1, // Default to 1 if null
                            'status' => $status,
                            'meta' => $meta,
                            'rec_date' => $legacyReport->rec_date,
                            'verify_date' => $legacyReport->verify_date,
                            'customer' => $legacyReport->customer,
                            'invoice_number' => $legacyReport->invoice_no,
                            'created_at' => $legacyReport->created_at,
                            'updated_at' => $legacyReport->updated_at,
                        ]);

                        foreach ($legacyReport->details as $detail) {
                            $newItem = VerificationItem::create([
                                'verification_report_id' => $newReport->id,
                                'part_name' => $detail->part_name,
                                'rec_quantity' => $detail->rec_quantity,
                                'verify_quantity' => $detail->verify_quantity,
                                'can_use' => $detail->can_use,
                                'cant_use' => $detail->cant_use,
                                'price' => $detail->price,
                                'currency' => $detail->currency ?: 'IDR',
                                'created_at' => $detail->created_at,
                                'updated_at' => $detail->updated_at,
                            ]);

                            foreach ($detail->defects as $defect) {
                                // Map source
                                $source = DefectSource::DAIJO;
                                if ($defect->is_customer) {
                                    $source = DefectSource::CUSTOMER;
                                } elseif ($defect->is_supplier) {
                                    $source = DefectSource::SUPPLIER;
                                }

                                VerificationItemDefect::create([
                                    'verification_item_id' => $newItem->id,
                                    'code' => null,
                                    'name' => $defect->category?->name ?? 'Unknown Defect',
                                    'severity' => Severity::LOW, // Default to LOW
                                    'source' => $source,
                                    'quantity' => $defect->quantity,
                                    'notes' => $defect->remarks,
                                    'created_at' => $defect->created_at,
                                    'updated_at' => $defect->updated_at,
                                ]);
                            }
                        }

                        // Create the ApprovalRequest
                        $approvalRequest = ApprovalRequest::create([
                            'approvable_type' => VerificationReport::class,
                            'approvable_id' => $newReport->id,
                            'status' => $status,
                            'rule_template_id' => $ruleTemplate->id,
                            'rule_template_version_id' => $ruleTemplate->id,
                            'current_step' => 1, // Will update below
                            'submitted_by' => $creatorId ?? 1,
                            'submitted_at' => $legacyReport->created_at,
                            'created_at' => $legacyReport->created_at,
                            'updated_at' => $legacyReport->updated_at,
                        ]);

                        // Step 1: Leader
                        $step1Status = 'PENDING';
                        $step1ActedBy = null;
                        $step1ActedAt = null;
                        $step1SignaturePath = null;
                        $step1UserSnapshot = null;

                        if ($legacyReport->autograph_2) {
                            $step1Status = 'APPROVED';
                            $signer = \App\Infrastructure\Persistence\Eloquent\Models\User::where('name', $legacyReport->autograph_user_2)->first();
                            $step1ActedBy = $signer?->id;
                            $step1ActedAt = $legacyReport->updated_at;
                            $step1SignaturePath = $legacyReport->autograph_2;
                            $step1UserSnapshot = $legacyReport->autograph_user_2;
                        }

                        $step1 = $approvalRequest->steps()->create([
                            'sequence' => 1,
                            'approver_type' => 'role',
                            'approver_id' => $leaderRole?->id,
                            'approver_snapshot_role_slug' => 'leader',
                            'approver_snapshot_label' => 'Leader',
                            'approver_snapshot_name' => $step1UserSnapshot,
                            'status' => $step1Status,
                            'acted_by' => $step1ActedBy,
                            'acted_at' => $step1ActedAt,
                            'signature_image_path' => $step1SignaturePath,
                            'created_at' => $legacyReport->created_at,
                            'updated_at' => $step1ActedAt ?? $legacyReport->created_at,
                        ]);

                        // Step 2: Department Head
                        $step2Status = 'PENDING';
                        $step2ActedBy = null;
                        $step2ActedAt = null;
                        $step2SignaturePath = null;
                        $step2UserSnapshot = null;

                        if ($legacyReport->autograph_3) {
                            $step2Status = 'APPROVED';
                            $signer = \App\Infrastructure\Persistence\Eloquent\Models\User::where('name', $legacyReport->autograph_user_3)->first();
                            $step2ActedBy = $signer?->id;
                            $step2ActedAt = $legacyReport->updated_at;
                            $step2SignaturePath = $legacyReport->autograph_3;
                            $step2UserSnapshot = $legacyReport->autograph_user_3;
                        }

                        $step2 = $approvalRequest->steps()->create([
                            'sequence' => 2,
                            'approver_type' => 'role',
                            'approver_id' => $deptHeadRole?->id,
                            'approver_snapshot_role_slug' => 'department-head',
                            'approver_snapshot_label' => 'Department Head',
                            'approver_snapshot_name' => $step2UserSnapshot,
                            'status' => $step2Status,
                            'acted_by' => $step2ActedBy,
                            'acted_at' => $step2ActedAt,
                            'signature_image_path' => $step2SignaturePath,
                            'final' => true,
                            'created_at' => $legacyReport->created_at,
                            'updated_at' => $step2ActedAt ?? $legacyReport->created_at,
                        ]);

                        // Determine current step and apply rejection status if needed
                        $currentStep = 1;
                        if ($status === 'APPROVED') {
                            $currentStep = 2;
                        } elseif ($status === 'IN_REVIEW' || $status === 'REJECTED') {
                            if ($legacyReport->autograph_2) {
                                $currentStep = 2;
                                if ($status === 'REJECTED') {
                                    $step2->update(['status' => 'REJECTED', 'acted_at' => $legacyReport->updated_at]);
                                }
                            } else {
                                $currentStep = 1;
                                if ($status === 'REJECTED') {
                                    $step1->update(['status' => 'REJECTED', 'acted_at' => $legacyReport->updated_at]);
                                }
                            }
                        }

                        $approvalRequest->update(['current_step' => $currentStep]);
                    }

                    $migrated++;
                }

                if ($dryRun) {
                    // Rollback transaction just in case anything tried to save
                    throw new \RuntimeException('Dry-run rollback');
                }
            });
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'Dry-run rollback') {
                throw $e;
            }
        }

        $this->info("Migration completed successfully.");
        $this->info("Migrated/Processed: {$migrated}");
        $this->info("Skipped (already migrated): {$skipped}");

        return Command::SUCCESS;
    }
}
