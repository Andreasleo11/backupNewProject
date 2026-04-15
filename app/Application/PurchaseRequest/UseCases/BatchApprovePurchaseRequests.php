<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Jobs\ProcessPurchaseRequestApproval;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

/**
 * Use Case: Batch approve multiple purchase requests synchronously.
 * Used by Directors to approve multiple PRs at once.
 */
final class BatchApprovePurchaseRequests
{
    /**
     * @param array<int|string> $purchaseRequestIds
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

        DB::beginTransaction();
        try {
            foreach ($purchaseRequestIds as $prId) {
                try {
                    Bus::dispatchSync(new ProcessPurchaseRequestApproval((int) $prId, $actorUserId, $remarks));
                    $approved++;
                } catch (\Exception $e) {
                    $failed++;
                    $errors[] = "PR {$prId}: {$e->getMessage()}";
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Batch approval completed: {$approved} approved" . ($failed > 0 ? ", {$failed} failed" : ''),
                'approved' => $approved,
                'failed' => $failed,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Batch approval failed: ' . $e->getMessage(),
                'approved' => $approved,
                'failed' => $failed,
                'errors' => $errors,
            ];
        }
    }
}
