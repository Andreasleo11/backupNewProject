<?php

namespace App\Application\PurchaseRequest\Queries;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\Services\PurchaseRequestDetailCalculator;
use App\Application\PurchaseRequest\ViewModels\PurchaseRequestDetailVM;
use App\Application\Signature\UseCases\GetDefaultActiveUserSignature;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\File;
use App\Models\MasterDataPr;
use App\Models\PurchaseRequest;

final class GetPurchaseRequestDetail
{
    public function __construct(
        private readonly PurchaseRequestDetailCalculator $calc,
        private readonly Approvals $approvals,
        private readonly GetDefaultActiveUserSignature $getDefaultSignature,
        private readonly \App\Domain\PurchaseRequest\Services\PurchaseRequestSecurityService $security,
    ) {}

    public function handle(int $prId, User $actor): PurchaseRequestDetailVM
    {
        /** @var PurchaseRequest $pr */
        $pr = PurchaseRequest::byRole($actor)
            ->with([
                'itemDetail.master',
                'createdBy',
                'files',
                'approvalRequest.steps',
                'fromDepartment',
            ])
            ->findOrFail($prId);

        $departments = Department::query()->get();

        // from dept no
        $fromDeptNo = (string) optional($pr->fromDepartment)->dept_no;
        if ($fromDeptNo === '') {
            // fallback legacy if relation missing
            $fromDept = Department::query()->where('name', $pr->from_department)->first();
            $fromDeptNo = (string) ($fromDept?->dept_no ?? '');
        }

        $approval = $pr->approvalRequest;

        // filter itemDetail based on roles/status
        $filtered = $this->calc->filteredItemDetail($actor, $pr);

        // Authorization & UI Flags (Refactored from PurchaseRequestPermissions)
        $flags = $this->buildFlags($actor, $pr);

        // Security: Sensitive Data (Prices)
        $canSeePrices = $this->security->canViewSensitiveData($actor, $pr);
        if (!$canSeePrices) {
            foreach ($filtered as $d) {
                $d->price = 0;
            }
        }

        // optional: legacy master item update when APPROVED (same as old show)
        if ($pr->workflow_status === 'APPROVED') {
            foreach ($filtered as $d) {
                $existing = MasterDataPr::where('name', $d->item_name)->first();
                if (! $existing) {
                    MasterDataPr::create([
                        'name' => $d->item_name,
                        'currency' => $d->currency,
                        'price' => $d->price,
                    ]);
                } else {
                    $existing->update([
                        'price' => $existing->latest_price,
                        'latest_price' => $d->price,
                    ]);
                }
            }
        }

        $totals = $this->calc->totals($pr, $filtered);
        if (!$canSeePrices) {
            $totals['total'] = 0;
            $totals['currency'] = null;
        }

        // files (you used doc_num before)
        $files = File::query()->where('doc_id', $pr->doc_num)->get();

        return new PurchaseRequestDetailVM(
            purchaseRequest: $pr,
            departments: $departments,
            files: $files,
            filteredItemDetail: $filtered,
            approval: $approval,
            fromDeptNo: $fromDeptNo,
            totals: $totals,
            flags: $flags,
            meta: [
                'userCreatedBy' => $pr->createdBy,
            ],
        );
    }

    /**
     * Build UI capability flags using Laravel Policies (Infrastructure) 
     * and specialized services.
     */
    private function buildFlags(User $user, PurchaseRequest $pr): array
    {
        // 1. Approve: Workflow Engine Check + Policy Check
        $canApprove = false;
        if ($pr->approvalRequest) {
            $canApprove = $this->approvals->canAct($pr, (int) $user->id)
                          && $user->can('approve', $pr);
        }

        // 2. Sign & Submit: Delegate to Security Service
        $canSignAndSubmit = $this->security->canSubmit($pr, $user);

        // 3. Signature Metadata
        $defaultSig = $canSignAndSubmit
            ? $this->getDefaultSignature->execute((int) $user->id)
            : null;

        return [
            'canApprove' => $canApprove,
            'canEdit' => $user->can('update', $pr),
            'canAutoApprove' => $user->can('autoApprove', $pr),
            'canSignAndSubmit' => $canSignAndSubmit,
            'isOwner' => $this->security->isOwner($pr, $user),
            'canViewAuditLog' => $user->can('approval.view-log'),
            'showImportToggle' => $this->security->canSelectImportPath($pr->from_department, $pr->to_department?->value),
            'isImportType' => $this->security->canSelectImportPath($pr->from_department, $pr->to_department?->value),
            'hasDefaultSignature' => $defaultSig !== null,
            'defaultSignaturePath' => $defaultSig?->filePath,
        ];
    }
}
