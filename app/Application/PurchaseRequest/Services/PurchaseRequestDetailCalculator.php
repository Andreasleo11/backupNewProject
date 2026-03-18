<?php

namespace App\Application\PurchaseRequest\Services;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Support\Collection;

final class PurchaseRequestDetailCalculator
{
    public function filteredItemDetail(User $user, PurchaseRequest $pr): Collection
    {
        // `itemDetail` already eager loaded
        // The unified approval engine ensures the PR is only routed to the user when it's their turn
        return $pr->itemDetail;
    }

    public function totals(PurchaseRequest $pr, Collection $details): array
    {
        $total = 0.0;
        $hasDiff = false;
        $prevCurrency = null;

        foreach ($details as $d) {
            // detect diff
            if ($prevCurrency === null) {
                $prevCurrency = $d->currency;
            } elseif ($prevCurrency !== $d->currency) {
                $hasDiff = true;
            }

            $subtotal = (float) $d->quantity * (float) $d->price;

            // Simplified for Unified Approval
            if ($pr->workflow_status === 'APPROVED') {
                if ($d->is_approve) {
                    $total += $subtotal;
                }
            } else {
                // In review/draft: count all not explicitly rejected items (0 = rejected, null/1 = ok to show in total for now)
                if ($d->is_approve_by_head !== 0 && $d->is_approve_by_gm !== 0 && $d->is_approve_by_verificator !== 0 && $d->is_approve !== 0) {
                    $total += $subtotal;
                }
            }
        }

        return [
            'total' => $total,
            'currency' => $prevCurrency, // may be null
            'hasCurrencyDiff' => $hasDiff,
        ];
    }
}
