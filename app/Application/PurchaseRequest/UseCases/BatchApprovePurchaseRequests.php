<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Jobs\ProcessPurchaseRequestApproval;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Batch approve multiple purchase requests.
 * Used by Directors to approve multiple PRs at once.
 */
final class BatchApprovePurchaseRequests
{
    /**
     * @param array<int> $purchaseRequestIds
     * @return array{success: bool, message: string, batch_id: string|null, approved: int, failed: int, errors: array<string>}
     */
    public function handle(array $purchaseRequestIds, int $actorUserId, ?string $remarks = null): array
    {
        if (empty($purchaseRequestIds)) {
            return [
                'success' => false,
                'message' => 'No purchase requests selected for approval.',
                'batch_id' => null,
                'approved' => 0,
                'failed' => 0,
                'errors' => [],
            ];
        }

        $jobs = [];
        foreach ($purchaseRequestIds as $prId) {
            $jobs[] = new ProcessPurchaseRequestApproval($prId, $actorUserId, $remarks);
        }

        $batch = Bus::batch($jobs)
            ->name('Batch PR Approval')
            ->dispatch();

        return [
            'success' => true,
            'message' => 'Batch approval started in the background.',
            'batch_id' => $batch->id,
            'approved' => 0,
            'failed' => 0,
            'errors' => [],
        ];
    }
}
