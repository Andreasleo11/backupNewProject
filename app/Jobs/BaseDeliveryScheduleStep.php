<?php

namespace App\Jobs;

use App\Events\DeliveryScheduleStepProgressed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseDeliveryScheduleStep implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    abstract protected function executeStep();

    public function handle(): void
    {
        try {
            Log::info(static::class . ' started.');
            $this->executeStep();
            Log::info(static::class . ' completed successfully.');
            // After each success
            Event::dispatch(new DeliveryScheduleStepProgressed(static::class, 'completed'));
        } catch (Throwable $e) {
            Log::error(static::class . ' failed: ' . $e->getMessage());
            // In catch block:
            Event::dispatch(new DeliveryScheduleStepProgressed(static::class, 'failed'));
            throw $e;
        }
    }

    public function retryUntil()
    {
        return now()->addMinutes(10);
    }
}
