<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

/**
 * Builds approval context for submission to the approval workflow engine.
 *
 * The approval engine uses this context to determine which approval steps
 * are required based on department, branch, amount, etc.
 */
class PurchaseRequestApprovalContextBuilder
{
    /**
     * Build the approval context array.
     *
     * @param string $fromDepartment Requesting department
     * @param string $toDepartment Target department (PURCHASING, MAINTENANCE, etc.)
     * @param string $branch Branch location (JAKARTA, KARAWANG)
     * @param bool $isOffice Whether this is an office-type PR
     * @param array $items Array of PR items with price/quantity
     * @return array Approval context for the workflow engine
     */
    public function build(
        string $fromDepartment,
        string $toDepartment,
        string $branch,
        bool $isOffice,
        array $items
    ): array {
        $totalAmount = $this->calculateTotal($items);

        return [
            'from_department' => strtoupper($fromDepartment),
            'to_department' => strtoupper($toDepartment),
            'branch' => strtoupper($branch),
            'at_office' => $isOffice,
            'amount' => $totalAmount,
        ];
    }

    /**
     * Calculate total amount from PR items.
     *
     * @param array $items Array of items with 'price' and 'quantity'
     * @return float Total amount
     */
    private function calculateTotal(array $items): float
    {
        $total = 0.0;

        foreach ($items as $item) {
            $price = is_object($item) ? ($item->price ?? 0) : ($item['price'] ?? 0);
            $quantity = is_object($item) ? ($item->quantity ?? 0) : ($item['quantity'] ?? 0);

            $total += ((float) $price) * ((float) $quantity);
        }

        return $total;
    }

    /**
     * Build context from an existing PurchaseRequest model.
     *
     * @param \App\Models\PurchaseRequest $pr The purchase request
     * @return array Approval context
     */
    public function buildFromModel(\App\Models\PurchaseRequest $pr): array
    {
        return $this->build(
            fromDepartment: $pr->from_department ?? '',
            toDepartment: $pr->to_department?->value ?? '',
            branch: $pr->branch ?? '',
            isOffice: $pr->type === 'office',
            items: $pr->items->toArray()
        );
    }
}
