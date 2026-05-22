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

            // Authorization & Business Validation (Delegated to Policy)
            \Illuminate\Support\Facades\Gate::authorize('update', $pr);

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

            // Intelligently sync items to prevent delete/re-insert spam in Activity Log
            $oldDetails = $pr->items()->get();
            $itemsData = $this->buildItems($dto, $pr->from_department);
            
            $user = \App\Models\User::find($dto->updatedByUserId);
            $isPurchaser = $user?->hasRole('PURCHASER');

            foreach ($itemsData as $itemData) {
                $matchIndex = false;
                if (!empty($itemData['id'])) {
                    $matchIndex = $oldDetails->search(fn($detail) => $detail->id === (int) $itemData['id']);
                }

                $id = $itemData['id'] ?? null;
                unset($itemData['id']);
                
                if ($matchIndex !== false) {
                    // Update existing item. It naturally preserves approval states.
                    $matchedDetail = $oldDetails->pull($matchIndex);
                    
                    // Don't overwrite existing approval state with the default from buildItems
                    unset($itemData['is_approve_by_head']); 
                    
                    $matchedDetail->update($itemData);
                } else {
                    // New item
                    if ($isPurchaser) {
                        $itemData['is_approve_by_head'] = 1;
                        if ($pr->type === 'factory') {
                            $itemData['is_approve_by_gm'] = 1;
                        }
                    }
                    $pr->items()->create($itemData);
                }
            }

            // Delete any items that were removed
            foreach ($oldDetails as $oldDetail) {
                $oldDetail->delete();
            }

            // Dispatch event
            PurchaseRequestUpdated::dispatch($pr);

            return $pr->fresh();
        });
    }

    private function buildItems(UpdatePurchaseRequestDTO $dto, string $fromDepartment): array
    {
        $autoHeadApprove = in_array($fromDepartment, ['PERSONALIA', 'PLASTIC INJECTION', 'MAINTENANCE MACHINE'], true);

        return array_map(function ($item) use ($autoHeadApprove) {
            return [
                'id' => $item->id,
                'item_name' => $item->itemName,
                'quantity' => $item->quantity,
                'purpose' => $item->purpose,
                'price' => $item->price,
                'uom' => $item->uom,
                'currency' => $item->currency,
                'is_approve_by_head' => $autoHeadApprove ? 1 : null,
            ];
        }, $dto->items);
    }
}
