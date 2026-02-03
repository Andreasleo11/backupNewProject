<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;

final class PurchaseRequestDetailService
{
    /**
     * Update detail field.
     */
    public function updateDetail(int $detailId, string $fieldName, mixed $value): bool
    {
        $detail = DetailPurchaseRequest::find($detailId);

        if (! $detail) {
            return false;
        }

        $updateData = match ($fieldName) {
            'item_name' => ['item_name' => $value],
            'quantity' => ['quantity' => $value],
            'purpose' => ['purpose' => $value],
            'price' => ['price' => $this->parsePrice($value)],
            default => null,
        };

        if ($updateData) {
            $detail->update($updateData);

            return true;
        }

        return false;
    }

    /**
     * Update received quantity for detail.
     */
    public function updateReceivedQuantity(int $detailId, float $receivedQuantity): void
    {
        DetailPurchaseRequest::find($detailId)->update([
            'received_quantity' => $receivedQuantity,
        ]);
    }

    /**
     * Update all details to received.
     */
    public function updateAllReceivedQuantity(int $reportId): void
    {
        $pr = PurchaseRequest::find($reportId);

        DetailPurchaseRequest::where('report_id', $reportId)->update([
            'received_quantity' => $pr->quantity,
        ]);
    }

    /**
     * Parse price from formatted string.
     */
    private function parsePrice(string $value): int
    {
        // Remove all dots to handle multiple thousand separators
        $numericValue = str_replace('.', '', $value);

        // Extract numeric part using regular expression
        preg_match("/\d+(\.\d+)?/", $numericValue, $matches);

        // Get the first match which should be the numeric value
        $numericValue = $matches[0] ?? '0';

        // Convert to integer
        return (int) str_replace('.', '', $numericValue);
    }
}
