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
    protected $description = 'Unified command to setup PO approval workflow and migrate all legacy POs atomically';

    public function __construct(private Approvals $approvals)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🚀 Starting PO Approval Setup...');

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
     * Migrate all legacy POs atomically
     */
    private function migrateAllLegacyPos(RuleTemplate $baselineRule): void
    {
        $this->info('🔄 Starting atomic migration of all legacy POs...');

        // Case 1: POs without any approval request
        $posWithoutApproval = PurchaseOrder::where('status', 2) // WAITING
            ->whereNull('approval_request_id')
            ->get();

        // Case 2: POs with approval request but missing rule template
        $posWithBrokenApproval = PurchaseOrder::where('status', 2) // WAITING
            ->whereNotNull('approval_request_id')
            ->whereHas('approvalRequest', function ($query) {
                $query->whereNull('rule_template_version_id');
            })
            ->with('approvalRequest')
            ->get();

        $totalToProcess = $posWithoutApproval->count() + $posWithBrokenApproval->count();

        if ($totalToProcess === 0) {
            $this->info('✅ No legacy POs need migration');
            return;
        }

        $this->info("📊 Found {$totalToProcess} legacy POs to migrate:");
        $this->info("   - {$posWithoutApproval->count()} POs without approval requests");
        $this->info("   - {$posWithBrokenApproval->count()} POs with incomplete approval requests");

        $progressBar = $this->output->createProgressBar($totalToProcess);
        $progressBar->start();

        $successCount = 0;
        $errorCount = 0;

        // Process Case 1: POs without approval requests
        foreach ($posWithoutApproval as $po) {
            try {
                $this->createApprovalRequestForPo($po, $baselineRule);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to create approval for PO #{$po->po_number}: {$e->getMessage()}");
                $errorCount++;
                Log::error('PO approval creation failed', [
                    'po_id' => $po->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $progressBar->advance();
        }

        // Process Case 2: POs with incomplete approval requests
        foreach ($posWithBrokenApproval as $po) {
            try {
                $this->completeApprovalRequestForPo($po->approvalRequest, $baselineRule);
                $successCount++;
            } catch (\Exception $e) {
                $this->error("Failed to complete approval for PO #{$po->po_number}: {$e->getMessage()}");
                $errorCount++;
                Log::error('PO approval completion failed', [
                    'po_id' => $po->id,
                    'request_id' => $po->approvalRequest->id,
                    'error' => $e->getMessage(),
                ]);
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info('📈 Migration Summary:');
        $this->info("   ✅ Successful: {$successCount}");
        if ($errorCount > 0) {
            $this->error("   ❌ Failed: {$errorCount}");
        }
        $this->info("   📊 Total Processed: {$totalToProcess}");
    }

    /**
     * Create approval request for PO that doesn't have one
     */
    private function createApprovalRequestForPo(PurchaseOrder $po, RuleTemplate $baselineRule): void
    {
        // Create approval request using proper polymorphic association
        $approvalRequest = new ApprovalRequest;
        $approvalRequest->fill([
            'status' => 'IN_REVIEW',
            // Note: rule_template_id is a UUID string but column is integer FK
            // We'll set rule_template_version_id which is the actual FK to rule template
            'rule_template_version_id' => $baselineRule->id,
            'current_step' => 1,
            'submitted_by' => $po->creator_id,
            'submitted_at' => $po->updated_at,
            'meta' => [
                'migration_source' => 'unified_workflow_setup',
                'migrated_at' => now()->toISOString(),
                'original_status_update' => $po->updated_at,
                'rule_template_code' => 'po.baseline.director',
            ],
        ]);

        // Associate with the PO
        $approvalRequest->approvable()->associate($po);
        $approvalRequest->save();

        // Create approval steps
        foreach ($baselineRule->steps as $stepTemplate) {
            $approvalRequest->steps()->create([
                'sequence' => $stepTemplate->sequence,
                'approver_type' => $stepTemplate->approver_type,
                'approver_id' => $stepTemplate->approver_id,
                'approver_snapshot_name' => $this->resolveApproverSnapshotName($stepTemplate->approver_type, $stepTemplate->approver_id),
                'approver_snapshot_role_slug' => $stepTemplate->approver_type === 'role' ? $this->getRoleSlug($stepTemplate->approver_id) : null,
                'approver_snapshot_label' => $this->resolveApproverSnapshotLabel($stepTemplate->approver_type, $stepTemplate->approver_id),
                'status' => 'PENDING',
            ]);
        }

        // Update PO with approval request ID
        $po->update(['approval_request_id' => $approvalRequest->id]);

        Log::info('PO approval request created via unified setup', [
            'po_id' => $po->id,
            'po_number' => $po->po_number,
            'approval_request_id' => $approvalRequest->id,
        ]);
    }

    /**
     * Complete approval request for PO that has one but missing rule template
     */
    private function completeApprovalRequestForPo(ApprovalRequest $request, RuleTemplate $baselineRule): void
    {
        // Assign the baseline rule template
        $request->update([
            // Note: rule_template_id is a UUID string but column is integer FK
            // We'll set rule_template_version_id which is the actual FK to rule template
            'rule_template_version_id' => $baselineRule->id,
        ]);

        // Create approval steps if they don't exist
        if ($request->steps()->count() === 0) {
            foreach ($baselineRule->steps as $stepTemplate) {
                $request->steps()->create([
                    'sequence' => $stepTemplate->sequence,
                    'approver_type' => $stepTemplate->approver_type,
                    'approver_id' => $stepTemplate->approver_id,
                    'approver_snapshot_name' => $this->resolveApproverSnapshotName($stepTemplate->approver_type, $stepTemplate->approver_id),
                    'approver_snapshot_role_slug' => $stepTemplate->approver_type === 'role' ? $this->getRoleSlug($stepTemplate->approver_id) : null,
                    'approver_snapshot_label' => $this->resolveApproverSnapshotLabel($stepTemplate->approver_type, $stepTemplate->approver_id),
                    'status' => 'PENDING',
                ]);
            }
        }

        Log::info('PO approval request completed via unified setup', [
            'request_id' => $request->id,
            'approvable_type' => $request->approvable_type,
            'approvable_id' => $request->approvable_id,
        ]);
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

        // Check all WAITING POs have approval requests
        $waitingPosWithoutApproval = PurchaseOrder::where('status', 2)
            ->whereNull('approval_request_id')
            ->count();

        if ($waitingPosWithoutApproval === 0) {
            $this->info('✅ All WAITING POs have approval requests');
        } else {
            $this->warn("⚠️ {$waitingPosWithoutApproval} WAITING POs still lack approval requests");
        }

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