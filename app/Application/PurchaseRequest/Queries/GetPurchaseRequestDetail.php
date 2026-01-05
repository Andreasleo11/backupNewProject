<?php

namespace App\Application\PurchaseRequest\Queries;

use App\Application\PurchaseRequest\Services\PurchaseRequestDetailCalculator;
use App\Application\PurchaseRequest\Services\PurchaseRequestPermissions;
use App\Application\PurchaseRequest\ViewModels\PurchaseRequestDetailVM;
use App\Models\Department;
use App\Models\File;
use App\Models\MasterDataPr;
use App\Models\PurchaseRequest;
use App\Infrastructure\Persistence\Eloquent\Models\User;

final class GetPurchaseRequestDetail
{
    public function __construct(
        private readonly PurchaseRequestDetailCalculator $calc,
        private readonly PurchaseRequestPermissions $perms,
    ) {}

    public function handle(int $prId, User $actor): PurchaseRequestDetailVM
    {
        /** @var PurchaseRequest $pr */
        $pr = PurchaseRequest::query()
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

        // optional: legacy master item update when status==4 (same as old show)
        if ($pr->status === 4) {
            // (You can move this later into a Domain service; keep for now)
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
        $flags = $this->perms->flags($actor, $pr);

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
}
