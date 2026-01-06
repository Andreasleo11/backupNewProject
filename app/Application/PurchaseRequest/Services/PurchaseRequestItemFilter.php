<?php

namespace App\Application\PurchaseRequest\Services;

use App\Enums\ToDepartment;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Support\Collection;

final class PurchaseRequestItemFilter
{
    /**
     * Filter items based on user role and PR approval status
     */
    public function filterItemsForUser(User $user, PurchaseRequest $pr, Collection $items): Collection
    {
        $specificationName = $user->specification?->name;

        if ($specificationName === 'DIRECTOR') {
            return $this->filterForDirector($pr, $items);
        }

        if ($specificationName === 'VERIFICATOR') {
            return $this->filterForVerificator($pr, $items);
        }

        // Include all details for other roles
        return $items;
    }

    /**
     * Filter items for Director role
     */
    private function filterForDirector(PurchaseRequest $pr, Collection $items): Collection
    {
        return $items->filter(function ($detail) use ($pr) {
            if ($pr->type === 'factory') {
                if ($pr->to_department === ToDepartment::COMPUTER) {
                    return $detail->is_approve_by_head &&
                        $detail->is_approve_by_gm &&
                        $detail->is_approve_by_verificator;
                }

                return $detail->is_approve_by_head && $detail->is_approve_by_gm;
            }

            // Office type
            return $detail->is_approve_by_head && $detail->is_approve_by_verificator;
        })->values();
    }

    /**
     * Filter items for Verificator role
     */
    private function filterForVerificator(PurchaseRequest $pr, Collection $items): Collection
    {
        return $items->filter(function ($detail) use ($pr) {
            if (
                $pr->to_department === ToDepartment::COMPUTER &&
                $pr->type === 'factory'
            ) {
                return $detail->is_approve_by_head && $detail->is_approve_by_gm;
            }

            return $detail->is_approve_by_head;
        })->values();
    }
}
