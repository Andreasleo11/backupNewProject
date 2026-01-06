<?php

namespace App\Application\PurchaseRequest\Services;

use App\Models\PurchaseRequest;

final class PurchaseRequestContextBuilder
{
    public function build(PurchaseRequest $pr): array
    {
        // ensure canonical values
        $amount = (float) ($pr->itemDetail?->sum(fn ($d) => $d->quantity * $d->price) ?? 0);

        return [
            'from_department' => $pr->from_department,
            'to_department' => $pr->to_department,
            'branch' => $pr->branch,
            'at_office' => $pr->type === 'office',
            'is_design' => (bool) ($pr->is_import ?? false) ? true : (bool) ($pr->is_design ?? false),
            'amount' => $amount,
        ];
    }
}
