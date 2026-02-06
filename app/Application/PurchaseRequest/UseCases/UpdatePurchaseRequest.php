<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\UpdatePurchaseRequestDTO;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Events\PurchaseRequestUpdated;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

final class UpdatePurchaseRequest
{
    public function __construct(
        private PurchaseRequestRepository $repo,
        private Approvals $approvals,
    ) {}

    public function handle(UpdatePurchaseRequestDTO $dto): PurchaseRequest
    {
        return DB::transaction(function () use ($dto) {
            $pr = $this->repo->find($dto->purchaseRequestId);

            if (! $pr) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
            }

            // Build update data
            $updateData = [
                'to_department' => $dto->toDepartment,
                'branch' => $dto->branch,
                'date_pr' => $dto->datePr,
                'date_required' => $dto->dateRequired,
                'remark' => $dto->remark,
                'supplier' => $dto->supplier,
                'pic' => $dto->pic,
                'is_import' => $dto->isImport ?? false,
                'updated_at' => now(),
            ];

            // Update PR header
            $pr->update($updateData);

            // Store old items for approval state preservation
            $oldDetails = DetailPurchaseRequest::where('purchase_request_id', $pr->id)->get();

            // Delete old items
            DetailPurchaseRequest::where('purchase_request_id', $pr->id)->delete();

            // Insert new items
            $items = $this->buildItems($dto, $pr->from_department);
            $this->repo->addItems($pr, $items);

            // Preserve approval states for matching items
            $this->preserveApprovalStates($pr, $oldDetails, $dto->updatedByUserId);

            // Dispatch event
            PurchaseRequestUpdated::dispatch($pr);

            return $pr->fresh();
        });
    }

    // Note: Removed shouldResetApproval - signature system handles state via immutable records
    // If approval workflow needs reset, it should be handled by approval system events

    private function buildItems(UpdatePurchaseRequestDTO $dto, string $fromDepartment): array
    {
        $autoHeadApprove = in_array($fromDepartment, ['PERSONALIA', 'PLASTIC INJECTION', 'MAINTENANCE MACHINE'], true);

        return array_map(function ($item) use ($autoHeadApprove) {
            return [
                'item_name' => $item->itemName,
                'quantity' => $item->quantity,
                'purpose' => $item->purpose,
                'price' => $item->price,
                'uom' => strtoupper($item->uom),
                'currency' => $item->currency,
                'is_approve_by_head' => $autoHeadApprove ? 1 : null,
            ];
        }, $dto->items);
    }

    private function preserveApprovalStates(PurchaseRequest $pr, $oldDetails, int $userId): void
    {
        $user = \App\Models\User::find($userId);
        $isPurchaser = $user?->hasRole('PURCHASER');

        $newDetails = DetailPurchaseRequest::where('purchase_request_id', $pr->id)->get();

        foreach ($newDetails as $newDetail) {
            foreach ($oldDetails as $oldDetail) {
                if ($newDetail->item_name === $oldDetail->item_name) {
                    // Same item - preserve approval states
                    $newDetail->update([
                        'is_approve_by_head' => $oldDetail->is_approve_by_head,
                        'is_approve_by_gm' => $oldDetail->is_approve_by_gm,
                        'is_approve_by_verificator' => $oldDetail->is_approve_by_verificator,
                    ]);
                } else {
                    // New/different item
                    $updates = [
                        'is_approve_by_head' => $isPurchaser ? 1 : $oldDetail->is_approve_by_head,
                    ];

                    if ($pr->type === 'factory') {
                        $updates['is_approve_by_gm'] = $isPurchaser ? 1 : $oldDetail->is_approve_by_gm;
                    }

                    $newDetail->update($updates);
                }
            }
        }
    }
}
