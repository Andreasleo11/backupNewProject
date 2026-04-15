<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Jobs\ProcessPurchaseRequestRejection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Bus;

/**
 * Use Case: Batch reject multiple purchase requests synchronously.
 * Used by Directors to reject multiple PRs at once.
 */
final class BatchRejectPurchaseRequests
{
    /**
     * @param array<int|string> $purchaseRequestIds
     * @return array{success: bool, message: string, rejected: int, failed: int, errors: array<string>}
     */
    public function handle(array $purchaseRequestIds, int $actorUserId, string $rejectionReason): array
    {
        if (empty($purchaseRequestIds)) {
            return [
                'success' => false,
                'message' => 'No purchase requests selected for rejection.',
                'rejected' => 0,
                'failed' => 0,
                'errors' => [],
            ];
        }

        if (empty(trim($rejectionReason))) {
            return [
                'success' => false,
                'message' => 'Rejection reason is required.',
                'rejected' => 0,
                'failed' => 0,
                'errors' => ['Rejection reason cannot be empty'],
            ];
        }

        $rejected = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($purchaseRequestIds as $prId) {
                try {
                    Bus::dispatchSync(new ProcessPurchaseRequestRejection((int) $prId, $actorUserId, $rejectionReason));
                    $rejected++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "PR {$prId}: {$e->getMessage()}";
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Batch rejection completed: {$rejected} rejected" . ($failed > 0 ? ", {$failed} failed" : ''),
                'rejected' => $rejected,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Batch rejection failed: ' . $e->getMessage(),
                'rejected' => $rejected,
                'failed' => $failed,
                'errors' => $errors,
            ];
        }
    }
}
