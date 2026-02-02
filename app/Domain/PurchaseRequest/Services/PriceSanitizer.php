<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

final class PriceSanitizer
{
    /**
     * Sanitize price string by removing currency symbols and formatting
     * 
     * Handles:
     * - Currency symbols: Rp, $, ¥
     * - Dots after currency symbols
     * - Whitespace
     * - Thousand separators (commas)
     * 
     * @param string|int|float|null $price Raw price input
     * @return float Sanitized numeric price
     */
    public function sanitize(string|int|float|null $price): float
    {
        // Handle null or empty
        if ($price === null || $price === '') {
            return 0.0;
        }
        
        // Convert to string for regex processing
        $priceStr = (string) $price;
        
        // Remove currency symbols (Rp, $, ¥) with optional dots and whitespace
        $cleaned = preg_replace('/[Rp$¥]\.?\s*/', '', $priceStr);
        
        // Remove thousand separators (commas)
        $cleaned = str_replace(',', '', $cleaned);
        
        // Convert to float
        return (float) $cleaned;
    }
    
    /**
     * Sanitize multiple prices at once
     * 
     * @param array<string|int|float|null> $prices
     * @return array<float>
     */
public function sanitizeMany(array $prices): array
    {
        return array_map(
            fn($price) => $this->sanitize($price),
            $prices
        );
    }
}
