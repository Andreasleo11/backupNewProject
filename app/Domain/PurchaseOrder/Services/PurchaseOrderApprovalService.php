<?php

declare(strict_types=1);

namespace App\Domain\PurchaseOrder\Services;

use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;

final class PurchaseOrderApprovalService
{
    /**
     * Approve detail by role type.
     */
    public function approveDetail(int $detailId, string $type): void
    {
        $updates = match ($type) {
            'head' => ['is_approve_by_head' => true],
            'verificator' => ['is_approve_by_verificator' => true],
            'director' => ['is_approve' => true],
            default => [],
        };

        if (! empty($updates)) {
            DetailPurchaseRequest::find($detailId)->update($updates);
        }
    }

    /**
     * Reject detail by role type.
     */
    public function rejectDetail(int $detailId, string $type): void
    {
        $updates = match ($type) {
            'head' => ['is_approve_by_head' => false],
            'verificator' => ['is_approve_by_verificator' => false],
            'director' => ['is_approve' => false],
            default => [],
        };

        if (! empty($updates)) {
            DetailPurchaseRequest::find($detailId)->update($updates);
        }
    }

    /**
     * Batch approve purchase requests.
     */
    public function batchApprove(array $ids, string $username, string $imageUrl): array
    {
        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'No records selected for approval.',
            ];
        }

        try {
            foreach ($ids as $id) {
                $this->approveSingleRequest($id, $username, $imageUrl);
            }

            return [
                'success' => true,
                'message' => 'Selected records approved successfully.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve selected records.',
            ];
        }
    }

    /**
     * Batch reject purchase requests.
     */
    public function batchReject(array $ids, string $rejectionReason): array
    {
        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'No records selected for rejection.',
            ];
        }

        try {
            foreach ($ids as $id) {
                $this->rejectSingleRequest($id, $rejectionReason);
            }

            return [
                'success' => true,
                'message' => 'Selected records rejected successfully.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to reject selected records.',
            ];
        }
    }

    /**
     * Approve single purchase request.
     */
    private function approveSingleRequest(int $id, string $username, string $imageUrl): void
    {
        $pr = PurchaseRequest::find($id);
        
        // Add signature using new signature system
        $signatureService = app(\App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService::class);
        $user = \App\Models\User::where('name', $username)->first();
        
        if ($user) {
            $signatureService->addSignature($pr, 'DIRECTOR', $user->id, $imageUrl);
        }

        $pr->update([
            'status' => 4,
            'approved_at' => now(),
        ]);

        // Determine which details to approve based on type
        $computerFactory = $pr->to_department->value === 'COMPUTER' && $pr->type === 'factory';

        if ($pr->type === 'factory' && ! $computerFactory) {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_gm', 1)
                ->get();
        } else {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_verificator', 1)
                ->get();
        }

        foreach ($details as $detail) {
            $detail->update(['is_approve' => 1]);
        }
    }

    /**
     * Reject single purchase request.
     */
    private function rejectSingleRequest(int $id, string $rejectionReason): void
    {
        $pr = PurchaseRequest::find($id);

        $pr->update([
            'status' => 5,
            'description' => $rejectionReason,
        ]);

        // Determine which details to reject based on type
        $computerFactory = $pr->to_department->value === 'COMPUTER' && $pr->type === 'factory';

        if ($pr->type === 'factory' && ! $computerFactory) {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_gm', 1)
                ->get();
        } else {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_verificator', 1)
                ->get();
        }

        foreach ($details as $detail) {
            $detail->update(['is_approve' => 0]);
        }
    }
}
