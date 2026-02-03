<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Use Case: Batch approve multiple purchase requests.
 * Used by Directors to approve multiple PRs at once.
 */
final class BatchApprovePurchaseRequests
{
    public function __construct(
        private readonly ApprovePurchaseRequest $approvePrUseCase
    ) {}

    /**
     * @param array<int> $purchaseRequestIds
     * @param int $actorUserId
     * @param string|null $remarks
     * @return array{success: bool, message: string, approved: int, failed: int, errors: array<string>}
     */
    public function handle(array $purchaseRequestIds, int $actorUserId, ?string $remarks = null): array
    {
        if (empty($purchaseRequestIds)) {
            return [
                'success' => false,
                'message' => 'No purchase requests selected for approval.',
                'approved' => 0,
                'failed' => 0,
                'errors' => [],
            ];
        }

        $approved = 0;
        $failed = 0;
        $errors = [];

        foreach ($purchaseRequestIds as $prId) {
            try {
                DB::beginTransaction();

                $dto = new ApprovalActionDTO(
                    purchaseRequestId: $prId,
                    actorUserId: $actorUserId,
                    remarks: $remarks
                );

                $this->approvePrUseCase->handle($dto);

                DB::commit();
                $approved++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $failed++;
                $errors[] = "PR #{$prId}: {$e->getMessage()}";

                Log::error('Batch approval failed for PR', [
                    'pr_id' => $prId,
                    'actor_user_id' => $actorUserId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return [
            'success' => $approved > 0,
            'message' => $this->buildResultMessage($approved, $failed),
            'approved' => $approved,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    private function buildResultMessage(int $approved, int $failed): string
    {
        if ($failed === 0) {
            return "Successfully approved {$approved} purchase request(s).";
        }

        if ($approved === 0) {
            return "Failed to approve all {$failed} purchase request(s).";
        }

        return "Approved {$approved} purchase request(s), {$failed} failed.";
    }
}
