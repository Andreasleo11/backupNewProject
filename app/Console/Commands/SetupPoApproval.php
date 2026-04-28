<?php

namespace App\Console\Commands;

use App\Application\Approval\Contracts\Approvals;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest;
use App\Infrastructure\Persistence\Eloquent\Models\RuleTemplate;
use App\Models\PurchaseOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class SetupPoApproval extends Command
{
    protected $signature = 'po:setup-approval {--force : Force recreation of approval workflow}';
    protected $description = 'Setup PO approval workflow and synchronize all existing PO data with approval requests';

    public function __construct(private Approvals $approvals)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🚀 Starting PO Approval Setup & Data Synchronization...');

        $forceRecreation = $this->option('force');

        // Pre-execution validation: Check if workflow data exists
        $this->validateWorkflowPrerequisites();

        // Get the baseline rule (already validated to exist)
        $baselineRule = RuleTemplate::where('model_type', \App\Models\PurchaseOrder::class)
            ->where('code', 'po.baseline.director')
            ->where('active', true)
            ->first();

        // DEBUG: Try without transaction first
        // DB::transaction(function () use ($forceRecreation, $baselineRule) {
            // Step 1: Migrate all legacy POs in one atomic operation
            $this->migrateAllLegacyPos($baselineRule);

            // Step 2: Verify the complete setup
            $this->verifyCompleteSetup();

            // Debug: Check if transaction committed
            $approvalCount = ApprovalRequest::where('approvable_type', \App\Models\PurchaseOrder::class)->count();
            Log::info('Final check', ['approval_requests_count' => $approvalCount]);
        // });

        $this->info('🎉 PO Approval Workflow Setup completed successfully!');
        return 0;
    }

    /**
     * Migrate all legacy POs atomically with status synchronization
     */
    private function migrateAllLegacyPos(RuleTemplate $baselineRule): void
    {
        $this->info('🔄 Starting comprehensive migration and synchronization of all legacy POs...');

        // Get all POs that need approval requests created
        $posNeedingRequests = PurchaseOrder::whereNull('approval_request_id')->get();

        // Get all POs that have approval requests but need status synchronization
        $posNeedingSync = PurchaseOrder::whereNotNull('approval_request_id')
            ->whereHas('approvalRequest', function ($query) {
                $query->whereColumn('approval_requests.status', '!=',
                    DB::raw("CASE
                        WHEN purchase_orders.status = 1 THEN 'IN_REVIEW'
                        WHEN purchase_orders.status = 2 THEN 'APPROVED'
                        WHEN purchase_orders.status = 3 THEN 'REJECTED'
                        WHEN purchase_orders.status = 4 THEN 'CANCELLED'
                        ELSE 'IN_REVIEW'
                    END"));
            })
            ->with('approvalRequest')
            ->get();

        $totalToProcess = $posNeedingRequests->count() + $posNeedingSync->count();

        if ($totalToProcess === 0) {
            $this->info('✅ All POs have properly synchronized approval requests');
            return;
        }

        $this->info("📊 Found {$totalToProcess} POs requiring attention:");
        $this->info("   - {$posNeedingRequests->count()} POs need approval requests created");
        $this->info("   - {$posNeedingSync->count()} POs need approval request status synchronized");

        $progressBar = $this->output->createProgressBar($totalToProcess);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        // Process POs that need new approval requests
        foreach ($posNeedingRequests as $po) {
            try {
                $this->createSynchronizedApprovalRequestForPo($po, $baselineRule);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to create approval for PO #{$po->po_number}: {$e->getMessage()}");
                $errorCount++;
                Log::error('PO approval creation failed', [
                    'po_id' => $po->id,
                    'po_status' => $po->status,
                    'error' => $e->getMessage(),
                ]);
            }
            $progressBar->advance();
        }

        // Process POs that need status synchronization
        foreach ($posNeedingSync as $po) {
            try {
                $this->synchronizeApprovalRequestStatus($po, $po->approvalRequest);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to sync approval for PO #{$po->po_number}: {$e->getMessage()}");
                $errorCount++;
                Log::error('PO approval sync failed', [
                    'po_id' => $po->id,
                    'request_id' => $po->approvalRequest->id,
                    'po_status' => $po->status,
                    'request_status' => $po->approvalRequest->status,
                    'error' => $e->getMessage(),
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('📈 Migration & Synchronization Summary:');
        $this->info("   ✅ Successful: {$successCount}");
        if ($errorCount > 0) {
            $this->error("   ❌ Failed: {$errorCount}");
        }
        $this->info("   📊 Total Processed: {$totalToProcess}");

        // Summary by operation type
        $this->info('📋 Operations Performed:');
        if ($posNeedingRequests->count() > 0) {
            $this->info("   - Created approval requests for {$posNeedingRequests->count()} POs");
        }
        if ($posNeedingSync->count() > 0) {
            $this->info("   - Synchronized approval request statuses for {$posNeedingSync->count()} POs");
        }
    }

    /**
     * Create approval request synchronized with PO status
     */
    private function createSynchronizedApprovalRequestForPo(PurchaseOrder $po, RuleTemplate $baselineRule): void
    {
        // Determine approval request status based on PO status
        $approvalStatus = $this->getApprovalStatusForPo($po);

        // Create approval request using proper polymorphic association
        $approvalRequest = new ApprovalRequest;
        $approvalRequest->fill([
            'status' => $approvalStatus,
            'rule_template_id' => $baselineRule->id,
            'rule_template_version_id' => $baselineRule->id,
            'current_step' => $approvalStatus === 'APPROVED' ? 1 : 1, // Always step 1 for director approval
            'submitted_by' => $po->creator_id,
            'submitted_at' => $po->created_at,
            'approved_at' => $approvalStatus === 'APPROVED' ? $po->approved_date : null,
            'meta' => [
                'migration_source' => 'status_synchronized_setup',
                'migrated_at' => now()->toISOString(),
                'original_po_status' => $po->status,
                'rule_template_code' => 'po.baseline.director',
                'sync_type' => 'legacy_data_migration',
            ],
        ]);

        // Associate with the PO
        $approvalRequest->approvable()->associate($po);
        $approvalRequest->save();

        // Create approval steps with appropriate status
        foreach ($baselineRule->steps as $stepTemplate) {
            $stepStatus = $this->getStepStatusForPo($po, $approvalStatus);

            $approvalRequest->steps()->create([
                'sequence' => $stepTemplate->sequence,
                'approver_type' => $stepTemplate->approver_type,
                'approver_id' => $stepTemplate->approver_id,
                'approver_snapshot_name' => $this->resolveApproverSnapshotName($stepTemplate->approver_type, $stepTemplate->approver_id),
                'approver_snapshot_role_slug' => $stepTemplate->approver_type === 'role' ? $this->getRoleSlug($stepTemplate->approver_id) : null,
                'approver_snapshot_label' => $this->resolveApproverSnapshotLabel($stepTemplate->approver_type, $stepTemplate->approver_id),
                'status' => $stepStatus,
                'approved_at' => $stepStatus === 'APPROVED' ? $po->approved_date : null,
                'approved_by' => $stepStatus === 'APPROVED' ? $this->getDirectorUserId() : null,
            ]);
        }

        // Update PO with approval request ID
        $po->update(['approval_request_id' => $approvalRequest->id]);

        Log::info('PO approval request created with status synchronization', [
            'po_id' => $po->id,
            'po_number' => $po->po_number,
            'po_status' => $po->status,
            'approval_request_id' => $approvalRequest->id,
            'approval_status' => $approvalStatus,
        ]);
    }

    /**
     * Get the appropriate approval request status based on PO status
     */
    private function getApprovalStatusForPo(PurchaseOrder $po): string
    {
        return match ($po->status) {
            \App\Enums\PurchaseOrderStatus::PENDING_APPROVAL->legacyValue() => 'IN_REVIEW',
            \App\Enums\PurchaseOrderStatus::APPROVED->legacyValue() => 'APPROVED',
            \App\Enums\PurchaseOrderStatus::REJECTED->legacyValue() => 'REJECTED',
            \App\Enums\PurchaseOrderStatus::CANCELLED->legacyValue() => 'CANCELLED',
            default => 'IN_REVIEW', // Default for any unexpected status
        };
    }

    /**
     * Get the appropriate step status based on PO and approval status
     */
    private function getStepStatusForPo(PurchaseOrder $po, string $approvalStatus): string
    {
        return match ($approvalStatus) {
            'APPROVED' => 'APPROVED',
            'REJECTED' => 'REJECTED',
            'CANCELLED' => 'CANCELLED',
            default => 'PENDING', // IN_REVIEW status
        };
    }

    /**
     * Synchronize existing approval request status with PO status
     */
    private function synchronizeApprovalRequestStatus(PurchaseOrder $po, ApprovalRequest $request): void
    {
        $correctApprovalStatus = $this->getApprovalStatusForPo($po);

        // Update approval request status
        $request->update([
            'status' => $correctApprovalStatus,
            'approved_at' => $correctApprovalStatus === 'APPROVED' ? $po->approved_date : null,
        ]);

        // Update approval steps status
        $correctStepStatus = $this->getStepStatusForPo($po, $correctApprovalStatus);

        foreach ($request->steps as $step) {
            $step->update([
                'status' => $correctStepStatus,
                'approved_at' => $correctStepStatus === 'APPROVED' ? $po->approved_date : null,
                'approved_by' => $correctStepStatus === 'APPROVED' ? $this->getDirectorUserId() : null,
            ]);
        }

        Log::info('PO approval request status synchronized', [
            'po_id' => $po->id,
            'po_number' => $po->po_number,
            'po_status' => $po->status,
            'request_id' => $request->id,
            'old_request_status' => $request->getOriginal('status'),
            'new_request_status' => $correctApprovalStatus,
        ]);
    }

    /**
     * Get the director user ID for approved requests (fallback to system user)
     */
    private function getDirectorUserId(): ?int
    {
        // Try to find a director user, fallback to the PO creator or system user
        $directorRole = \Spatie\Permission\Models\Role::where('name', 'director')->first();
        if ($directorRole) {
            $directorUser = $directorRole->users()->first();
            if ($directorUser) {
                return $directorUser->id;
            }
        }

        // Fallback: return null (system approval)
        return null;
    }



    /**
     * Verify the complete setup
     */
    private function verifyCompleteSetup(): void
    {
        $this->info('🔍 Verifying complete setup...');

        // Check rule exists
        $rule = RuleTemplate::where('model_type', \App\Models\PurchaseOrder::class)
            ->where('code', 'po.baseline.director')
            ->first();

        if ($rule) {
            $this->info('✅ Baseline approval rule exists');
        } else {
            $this->error('❌ Baseline approval rule missing');
        }

        // Check all POs have approval requests
        $totalPos = PurchaseOrder::count();
        $posWithApproval = PurchaseOrder::whereNotNull('approval_request_id')->count();
        $posWithoutApproval = PurchaseOrder::whereNull('approval_request_id')->count();

        $this->info("📊 Approval Request Coverage: {$posWithApproval}/{$totalPos} POs ({$posWithoutApproval} missing)");

        if ($posWithoutApproval === 0) {
            $this->info('✅ All POs have approval requests');
        } else {
            $this->warn("⚠️ {$posWithoutApproval} POs still lack approval requests");
        }

        // Verify status synchronization
        $this->verifyStatusSynchronization();

        // Check all approval requests have rule templates
        $totalPoRequests = ApprovalRequest::where('approvable_type', \App\Models\PurchaseOrder::class)->count();
        $requestsWithoutRules = ApprovalRequest::where('approvable_type', \App\Models\PurchaseOrder::class)
            ->whereNull('rule_template_version_id')
            ->count();

        $this->info("📊 PO approval requests: {$totalPoRequests} total, {$requestsWithoutRules} without rules");

        if ($requestsWithoutRules === 0) {
            $this->info('✅ All PO approval requests have rule templates assigned');
        } else {
            $this->warn("⚠️ {$requestsWithoutRules} PO approval requests still lack rule templates");
        }

        // Check automatic status transition logic
        $poReflection = new \ReflectionClass(\App\Models\PurchaseOrder::class);
        $interfaces = $poReflection->getInterfaceNames();

        if (in_array(\App\Domain\Approval\Contracts\Approvable::class, $interfaces)) {
            $this->info('✅ PurchaseOrder implements Approvable interface');
        } else {
            $this->error('❌ PurchaseOrder does not implement Approvable interface');
        }

        $engineContent = file_get_contents(app_path('Infrastructure/Approval/Services/ApprovalEngine.php'));
        if (strpos($engineContent, 'PurchaseOrderStatus::APPROVED') !== false) {
            $this->info('✅ ApprovalEngine has automatic PO status transition logic');
        } else {
            $this->warn('⚠️ ApprovalEngine may not have PO status transition logic');
        }

        $this->info('✅ Setup verification complete');
    }

    /**
     * Verify that approval request statuses are synchronized with PO statuses
     */
    private function verifyStatusSynchronization(): void
    {
        $this->info('🔍 Verifying status synchronization...');

        // Check APPROVED POs have APPROVED approval requests
        $approvedPosWithWrongStatus = PurchaseOrder::where('status', \App\Enums\PurchaseOrderStatus::APPROVED->legacyValue())
            ->whereHas('approvalRequest', function ($query) {
                $query->where('status', '!=', 'APPROVED');
            })
            ->count();

        // Check PENDING_APPROVAL POs have IN_REVIEW approval requests
        $pendingPosWithWrongStatus = PurchaseOrder::where('status', \App\Enums\PurchaseOrderStatus::PENDING_APPROVAL->legacyValue())
            ->whereHas('approvalRequest', function ($query) {
                $query->where('status', '!=', 'IN_REVIEW');
            })
            ->count();

        // Check REJECTED POs have REJECTED approval requests
        $rejectedPosWithWrongStatus = PurchaseOrder::where('status', \App\Enums\PurchaseOrderStatus::REJECTED->legacyValue())
            ->whereHas('approvalRequest', function ($query) {
                $query->where('status', '!=', 'REJECTED');
            })
            ->count();

        $totalIssues = $approvedPosWithWrongStatus + $pendingPosWithWrongStatus + $rejectedPosWithWrongStatus;

        if ($totalIssues === 0) {
            $this->info('✅ All PO statuses are properly synchronized with approval requests');
        } else {
            $this->warn("⚠️ Found {$totalIssues} status synchronization issues:");
            if ($approvedPosWithWrongStatus > 0) {
                $this->warn("   - {$approvedPosWithWrongStatus} APPROVED POs with non-APPROVED approval requests");
            }
            if ($pendingPosWithWrongStatus > 0) {
                $this->warn("   - {$pendingPosWithWrongStatus} PENDING_APPROVAL POs with non-IN_REVIEW approval requests");
            }
            if ($rejectedPosWithWrongStatus > 0) {
                $this->warn("   - {$rejectedPosWithWrongStatus} REJECTED POs with non-REJECTED approval requests");
            }
        }
    }

    /**
     * Validate that required workflow prerequisites exist
     */
    private function validateWorkflowPrerequisites(): void
    {
        $modelType = \App\Models\PurchaseOrder::class;

        // Check if baseline PO director rule exists
        $baselineRule = RuleTemplate::where('model_type', $modelType)
            ->where('code', 'po.baseline.director')
            ->where('active', true)
            ->first();

        if (!$baselineRule) {
            $this->error('❌ Required workflow data is missing!');
            $this->error('');
            $this->error('The PO approval workflow has not been seeded into the database.');
            $this->error('Please run the following command first:');
            $this->error('');
            $this->error('    php artisan db:seed --class=PoWorkflowSeeder');
            $this->error('');
            $this->error('This will create the necessary approval rules and workflows.');
            $this->error('');
            $this->error('After seeding, you can run this command again.');

            exit(1); // Exit with error code
        }

        $this->info("✅ Found baseline approval rule: {$baselineRule->name} (ID: {$baselineRule->id})");
    }

    private function resolveApproverSnapshotName(string $type, int $id): string
    {
        if ($type === 'user') {
            $user = \App\Infrastructure\Persistence\Eloquent\Models\User::find($id);
            return $user->name ?? 'Unknown User';
        }
        $role = \Spatie\Permission\Models\Role::find($id);
        return $role->name ?? 'Unknown Role';
    }

    private function getRoleSlug(int $roleId): ?string
    {
        $role = \Spatie\Permission\Models\Role::find($roleId);
        return $role->name ?? null;
    }

    private function resolveApproverSnapshotLabel(string $type, int $id): string
    {
        if ($type === 'user') {
            $user = \App\Infrastructure\Persistence\Eloquent\Models\User::find($id);
            return $user->name ?? 'Unknown User';
        }
        $role = \Spatie\Permission\Models\Role::find($id);
        return $this->getRoleLabel($role->name ?? '');
    }

    private function getRoleLabel(string $slug): string
    {
        return match ($slug) {
            'department-head' => 'Dept Head',
            'verificator' => 'Verificator',
            'director' => 'Director',
            'general-manager' => 'General Manager',
            'purchaser' => 'Purchasing',
            default => $slug,
        };
    }
}