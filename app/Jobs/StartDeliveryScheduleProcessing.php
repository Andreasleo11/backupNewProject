<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Bus;
use App\Jobs\{
    ProcessDeliveryScheduleStep1,
    ProcessDeliveryScheduleStep2,
    ProcessDeliveryScheduleStep3,
    ProcessDeliveryScheduleStep4,
};

class StartDeliveryScheduleProcessing
{
    public static function dispatchChain(): void
    {
        Bus::chain([
            new ProcessDeliveryScheduleStep1(),
            new ProcessDeliveryScheduleStep2(),
            new ProcessDeliveryScheduleStep3(),
            new ProcessDeliveryScheduleStep4(),
        ])->onQueue('default')->dispatch();
    }
}
