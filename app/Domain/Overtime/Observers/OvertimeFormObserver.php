<?php

namespace App\Domain\Overtime\Observers;

use App\Events\OvertimeStatusChanged;
use App\Domain\Overtime\Models\OvertimeForm;
use Illuminate\Support\Facades\Cache;

class OvertimeFormObserver
{
    public function updating(OvertimeForm $model): void
    {
        if ($model->isDirty('status')) {
            // Dispatch event asynchronously — listener handles email queuing.
            OvertimeStatusChanged::dispatch($model);
        }
    }

    /**
     * Handle the OvertimeForm "created" event.
     */
    public function created(OvertimeForm $OvertimeForm): void
    {
        Cache::forget('approval_flow_rules');
    }

    /**
     * Handle the OvertimeForm "updated" event.
     */
    public function updated(OvertimeForm $OvertimeForm): void {}

    /**
     * Handle the OvertimeForm "deleted" event.
     */
    public function deleted(OvertimeForm $OvertimeForm): void
    {
        //
    }

    /**
     * Handle the OvertimeForm "restored" event.
     */
    public function restored(OvertimeForm $OvertimeForm): void
    {
        //
    }

    /**
     * Handle the OvertimeForm "force deleted" event.
     */
    public function forceDeleted(OvertimeForm $OvertimeForm): void
    {
        //
    }
}

