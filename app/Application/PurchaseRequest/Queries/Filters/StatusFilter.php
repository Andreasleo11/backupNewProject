<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filter by workflow status (DRAFT, IN_REVIEW, APPROVED, REJECTED, CANCELED).
 * Translates UI-facing status strings into the correct approval_requests query.
 */
final class StatusFilter implements PurchaseRequestFilter
{
    public function __construct(private readonly string $status) {}

    public function apply(Builder $query): void
    {
        $query->where(function ($q) {
            match ($this->status) {
                'DRAFT' => $q->where(fn ($s) => $s->whereHas('approvalRequest', fn ($ar) => $ar->where('status', 'DRAFT'))
                    ->orWhereDoesntHave('approvalRequest')),
                'CANCELED' => $q->where('purchase_requests.is_cancel', 1),
                'IN_REVIEW' => $q->whereHas('approvalRequest', fn ($ar) => $ar->where('status', 'IN_REVIEW')),
                'APPROVED' => $q->whereHas('approvalRequest', fn ($ar) => $ar->where('status', 'APPROVED')),
                'REJECTED' => $q->whereHas('approvalRequest', fn ($ar) => $ar->where('status', 'REJECTED')),
                default => null,
            };
        });

        // For non-cancelled status queries, exclude hard-cancelled PRs
        if ($this->status !== 'CANCELED') {
            $query->where(
                fn ($q) => $q->whereNull('purchase_requests.is_cancel')
                    ->orWhere('purchase_requests.is_cancel', 0)
            );
        }
    }
}
