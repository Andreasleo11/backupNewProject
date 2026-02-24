<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Jobs\ProcessPurchaseRequestRejection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Batch reject multiple purchase requests.
 * Used by Directors to reject multiple PRs at once.
 */
final class BatchRejectPurchaseRequests
{
    /**
     * @param array<int> $purchaseRequestIds
     * @return array{success: bool, message: string, batch_id: string|null, rejected: int, failed: int, errors: array<string>}
     */
    public function handle(array $purchaseRequestIds, int $actorUserId, string $rejectionReason): array
    {
        if (empty($purchaseRequestIds)) {
            return [
                'success' => false,
                'message' => 'No purchase requests selected for rejection.',
                'batch_id' => null,
                'rejected' => 0,
                'failed' => 0,
                'errors' => [],
            ];
        }

        if (empty(trim($rejectionReason))) {
            return [
                'success' => false,
                'message' => 'Rejection reason is required.',
                'batch_id' => null,
                'rejected' => 0,
                'failed' => 0,
                'errors' => ['Rejection reason cannot be empty'],
            ];
        }

        $jobs = [];
        foreach ($purchaseRequestIds as $prId) {
            $jobs[] = new ProcessPurchaseRequestRejection($prId, $actorUserId, $rejectionReason);
        }

        $batch = Bus::batch($jobs)
            ->name('Batch PR Rejection')
            ->dispatch();

        return [
            'success' => true,
            'message' => 'Batch rejection started in the background.',
            'batch_id' => $batch->id,
            'rejected' => 0,
            'failed' => 0,
            'errors' => [],
        ];
    }
}
