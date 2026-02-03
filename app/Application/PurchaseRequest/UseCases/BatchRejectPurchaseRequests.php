<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Batch reject multiple purchase requests.
 * Used by Directors to reject multiple PRs at once.
 */
final class BatchRejectPurchaseRequests
{
    public function __construct(
        private readonly RejectPurchaseRequest $rejectPrUseCase
    ) {}

    /**
     * @param array<int> $purchaseRequestIds
     * @param int $actorUserId
     * @param string $rejectionReason
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

        foreach ($purchaseRequestIds as $prId) {
            try {
                DB::beginTransaction();

                $dto = new ApprovalActionDTO(
                    purchaseRequestId: $prId,
                    actorUserId: $actorUserId,
                    remarks: $rejectionReason
                );

                $this->rejectPrUseCase->handle($dto);

                DB::commit();
                $rejected++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $failed++;
                $errors[] = "PR #{$prId}: {$e->getMessage()}";

                Log::error('Batch rejection failed for PR', [
                    'pr_id' => $prId,
                    'actor_user_id' => $actorUserId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return [
            'success' => $rejected > 0,
            'message' => $this->buildResultMessage($rejected, $failed),
            'rejected' => $rejected,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function buildResultMessage(int $rejected, int $failed): string
    {
        if ($failed === 0) {
            return "Successfully rejected {$rejected} purchase request(s).";
        }

        if ($rejected === 0) {
            return "Failed to reject all {$failed} purchase request(s).";
        }

        return "Rejected {$rejected} purchase request(s), {$failed} failed.";
    }
}
