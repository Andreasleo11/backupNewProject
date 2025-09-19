<?php

namespace App\Observers;

use App\Models\Detail;
use App\Models\MasterDataPartPriceLog;

class DetailObserver
{
    public function created(Detail $detail): void
    {
        // dd($detail);
        $parts = explode("/", $detail->part_name);
        $part_code = $parts[0];

        $exists = MasterDataPartPriceLog::query()
            ->where("part_code", $part_code)
            ->where("currency", $detail->currency)
            ->where("price", $detail->price)
            ->first();

        if (!$exists) {
            MasterDataPartPriceLog::create([
                "report_id" => $detail->report_id ?? null,
                "detail_id" => $detail->id,
                "created_by" => auth()->id(),
                "part_code" => $part_code,
                "currency" => $detail->currency,
                "price" => $detail->price,
            ]);
        }
    }
}
