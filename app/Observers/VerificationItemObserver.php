<?php

namespace App\Observers;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationItem;
use App\Models\MasterDataPartPriceLog;

class VerificationItemObserver
{
    /**
     * Log part price on creation (replaces DetailObserver on legacy Detail model).
     * Uses verification_report_id / verification_item_id — not the old report_id / detail_id
     * columns which have FK constraints pointing at the deleted reports/details tables.
     */
    public function created(VerificationItem $item): void
    {
        $partCode = explode('/', $item->part_name)[0];

        $exists = MasterDataPartPriceLog::query()
            ->where('part_code', $partCode)
            ->where('currency', $item->currency)
            ->where('price', $item->price)
            ->exists();

        if (! $exists) {
            MasterDataPartPriceLog::create([
                'verification_report_id' => $item->verification_report_id,
                'verification_item_id'   => $item->id,
                'created_by'             => auth()->id(),
                'part_code'              => $partCode,
                'currency'               => $item->currency,
                'price'                  => $item->price,
            ]);
        }
    }
}
