<?php

namespace App\Application\PurchaseRequest\Services;

use App\Models\MasterDataPr;

final class MasterPrItemService
{
    /**
     * Update master PR items from purchase request items
     */
    public function updateFromItems(?array $items): void
    {
        if (! isset($items)) {
            return;
        }

        foreach ($items as $itemData) {
            $itemName = $itemData['item_name'];
            $price = $this->sanitizeCurrencyInput($itemData['price']);
            $currency = $itemData['currency'];

            // Check if the item exists in MasterDataPr
            $existingItem = MasterDataPr::where('name', $itemName)->first();

            if (! $existingItem) {
                // Item not available in MasterDataPr - create new
                MasterDataPr::create([
                    'name' => $itemName,
                    'currency' => $currency,
                    'price' => $price,
                ]);
            } else {
                // Item available - update prices
                $existingItem->update([
                    'price' => $existingItem->latest_price,
                    'latest_price' => $price,
                ]);
            }
        }
    }

    /**
     * Sanitize currency input by removing currency symbols and formatting
     */
    public function sanitizeCurrencyInput(mixed $value): float
    {
        $price = preg_replace('/[Rp$¥]\.?\s*/', '', (string) ($value ?? 0));

        return (float) str_replace(',', '', $price);
    }

    /**
     * Format decimal value - remove decimal point if it's an integer
     */
    public function formatDecimal(float $value): int|float
    {
        // Check if the number has no decimal part (i.e., is an integer)
        if (floor($value) == $value) {
            // If it's an integer, cast it to int to remove the decimal point
            return (int) $value;
        }

        // If it has a decimal part, return it as is
        return $value;
    }
}
