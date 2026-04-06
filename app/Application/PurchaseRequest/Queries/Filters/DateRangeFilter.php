<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filter by PR date range or exact date.
 * Parses the Flatpickr "from to to" date-range string format.
 */
final class DateRangeFilter implements PurchaseRequestFilter
{
    public function __construct(
        private readonly string $from,
        private readonly ?string $to = null,
    ) {}

    /**
     * Build from a raw Flatpickr date-range string (e.g. "2024-01-01 to 2024-12-31").
     */
    public static function fromString(string $raw): self
    {
        $parts = explode(' to ', $raw);

        return new self(
            from: trim($parts[0]),
            to:   isset($parts[1]) ? trim($parts[1]) : null,
        );
    }

    public function apply(Builder $query): void
    {
        if ($this->to) {
            $query->whereBetween('date_pr', [$this->from, $this->to]);
        } else {
            $query->whereDate('date_pr', $this->from);
        }
    }
}
