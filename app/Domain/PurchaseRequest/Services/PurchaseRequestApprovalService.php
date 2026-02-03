<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Application\PurchaseRequest\UseCases\BatchApprovePurchaseRequests;
use App\Application\PurchaseRequest\UseCases\BatchRejectPurchaseRequests;
use App\Models\User;

/**
 * Legacy service maintained for backward compatibility.
 * 
 * @deprecated This service is deprecated. Use the following instead:
 *   - For batch approval: BatchApprovePurchaseRequests use case
 *   - For batch rejection: BatchRejectPurchaseRequests use case
 *   - For item approval: ApproveItem use case via DetailPurchaseRequestController
 *   - For item rejection: RejectItem use case via DetailPurchaseRequestController
 */
final class PurchaseRequestApprovalService
{
    public function __construct(
        private readonly BatchApprovePurchaseRequests $batchApproveUseCase,
        private readonly BatchRejectPurchaseRequests $batchRejectUseCase
    ) {}

    /**
     * Batch approve purchase requests.
     * 
     * @deprecated Use BatchApprovePurchaseRequests use case directly
     * @param array<int> $ids
     * @param string $username
     * @param string $imageUrl
     * @return array{success: bool, message: string}
     */
    public function batchApprove(array $ids, string $username, string $imageUrl): array
    {
        $user = User::where('name', $username)->first();

        if (! $user) {
            return [
                'success' => false,
                'message' => 'User not found.',
            ];
        }

        $result = $this->batchApproveUseCase->handle($ids, $user->id);

        // Legacy response format
        return [
            'success' => $result['success'],
            'message' => $result['message'],
        ];
    }

    /**
     * Batch reject purchase requests.
     * 
     * @deprecated Use BatchRejectPurchaseRequests use case directly
     * @param array<int> $ids
     * @param string $rejectionReason
     * @return array{success: bool, message: string}
     */
    public function batchReject(array $ids, string $rejectionReason): array
    {
        $userId = auth()->id();

        if (! $userId) {
            return [
                'success' => false,
                'message' => 'User not authenticated.',
            ];
        }

        $result = $this->batchRejectUseCase->handle($ids, $userId, $rejectionReason);

        // Legacy response format
        return [
            'success' => $result['success'],
            'message' => $result['message'],
        ];
    }
}
