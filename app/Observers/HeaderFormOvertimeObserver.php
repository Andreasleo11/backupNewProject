<?php

namespace App\Observers;

use App\Models\HeaderFormOvertime;
use Illuminate\Support\Facades\Cache;

class HeaderFormOvertimeObserver
{
    public function updating(HeaderFormOvertime $model)
    {
        if ($model->isDirty('status')) {
            $model->sendNotification($model);
        }
    }
    /**
     * Handle the HeaderFormOvertime "created" event.
     */
    public function created(HeaderFormOvertime $headerFormOvertime): void
    {
        Cache::forget('approval_flow_rules');
    }

    /**
     * Handle the HeaderFormOvertime "updated" event.
     */
    public function updated(HeaderFormOvertime $headerFormOvertime): void {}

    /**
     * Handle the HeaderFormOvertime "deleted" event.
     */
    public function deleted(HeaderFormOvertime $headerFormOvertime): void
    {
        //
    }

    /**
     * Handle the HeaderFormOvertime "restored" event.
     */
    public function restored(HeaderFormOvertime $headerFormOvertime): void
    {
        //
    }

    /**
     * Handle the HeaderFormOvertime "force deleted" event.
     */
    public function forceDeleted(HeaderFormOvertime $headerFormOvertime): void
    {
        //
    }
}
