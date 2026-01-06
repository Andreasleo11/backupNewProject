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
        return $pr->itemDetail
            ->map(function ($detail) {
                // keep your existing formatting hook if needed later
                return $detail;
            })
            ->filter(function ($detail) use ($user, $pr) {
                // this was your legacy filtering inside show()

                if ($user->specification?->name === 'DIRECTOR') {
                    if ($pr->type === 'factory') {
                        if ($pr->to_department?->value === 'COMPUTER' || $pr->to_department === 'Computer') {
                            return $detail->is_approve_by_head
                                && $detail->is_approve_by_gm
                                && $detail->is_approve_by_verificator;
                        }

                        return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                    }

                    return $detail->is_approve_by_head && $detail->is_approve_by_verificator;
                }

                if ($user->specification?->name === 'VERIFICATOR') {
                    if (
                        ($pr->to_department?->value === 'COMPUTER' || $pr->to_department === 'Computer')
                        && $pr->type === 'factory'
                    ) {
                        return $detail->is_approve_by_head && $detail->is_approve_by_gm;
                    }

                    return $detail->is_approve_by_head;
                }

                return true;
            })
            ->values();
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

            // keep your current total rules (copied from blade)
            if ($pr->status === 6 || $pr->status === 7) {
                if (! is_null($d->is_approve_by_head)) {
                    if ($d->is_approve_by_head) {
                        $total += $subtotal;
                    }
                } else {
                    $total += $subtotal;
                }
            } elseif ($pr->status === 2) {
                if (! is_null($d->is_approve_by_verificator)) {
                    if ($d->is_approve_by_verificator) {
                        $total += $subtotal;
                    }
                } else {
                    if ($d->is_approve_by_head) {
                        $total += $subtotal;
                    }
                }
            } elseif ($pr->status === 3) {
                if (! is_null($d->is_approve)) {
                    if ($d->is_approve) {
                        $total += $subtotal;
                    }
                } else {
                    // keep as-is (mind enum vs string)
                    $toDept = $pr->to_department?->value ?? $pr->to_department;
                    if ($pr->type === 'office' || ($toDept === 'COMPUTER' && $pr->type === 'factory')) {
                        if ($d->is_approve_by_verificator) {
                            $total += $subtotal;
                        }
                    } elseif ($d->is_approve_by_gm) {
                        $total += $subtotal;
                    }
                }
            } elseif ($pr->status === 4) {
                if ($d->is_approve) {
                    $total += $subtotal;
                }
            } elseif ($pr->status === 1) {
                if (! is_null($d->is_approve_by_head)) {
                    if ($d->is_approve_by_head) {
                        $total += $subtotal;
                    }
                } else {
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
