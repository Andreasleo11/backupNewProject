<?php

namespace App\Listeners;

use App\Events\DeliveryScheduleStepProgressed;
use Illuminate\Support\Facades\Log;

class LogDeliveryScheduleProgress
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DeliveryScheduleStepProgressed $event): void
    {
        Log::info("Step {$event->stepClass} {$event->status}");
    }
}
