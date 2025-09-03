<?php
declare(strict_types=1);

namespace App\Services\Payroll\Progress;

use Illuminate\Support\Facades\Cache;

final class ProgressReporter
{
    private string $eventsKey;
    private string $stateKey;
    private int $maxEvents = 50;
    private array $diffKeys = ['phase', 'processed', 'total', 'percent', 'last_range', 'message', 'is_running'];

    public function __construct(private readonly string $companyArea) 
    {
        $this->stateKey = "sync_progress_{$companyArea}";
        $this->eventsKey = "sync_progress_events_{$companyArea}";
    }

    private function write(array $payload): void
    {
        $payload['updated'] = now('Asia/Jakarta')->toDateTimeString();
        
        $prev = Cache::get($this->stateKey);
        Cache::put($this->stateKey, $payload, now()->addMinutes(30));

        $diff = $this->diffAssoc($prev ?? [], $payload, $this->diffKeys);

        if($prev === null || !empty($diff)) {
            $event = [
                'ts' => $payload['updated'],
                'phase' => $payload['phase'] ?? 'unknown',
                'changes' => $prev === null ? $this->bootstrapChanges($payload) : $diff,
            ];

            $events = Cache::get($this->eventsKey, []);
            $events[] = $event;
            if(count($events) > $this->maxEvents) {
                $events = array_slice($events, -$this->maxEvents);
            }
            Cache::put($this->eventsKey, $events, now()->addMinutes(60));

        }
    }
    
    /** Make a diff map like ['processed'=>['from'=>12,'to'=>18], 'phase'=>['from'=>'employees','to'=>'annual_leave']] */
    private function diffAssoc(array $prev, array $curr, array $keys): array 
    {
        $changes = [];
        foreach ($keys as $k) {
            $prevVal = $prev[$k] ?? null;
            $currVal = $curr[$k] ?? null;

            if($prevVal !== $currVal){
                $changes[$k] = ['from' => $prevVal, 'to' => $currVal];
            }
        }
        return $changes;
    }

    /**First event: show all whitelisted fields as 'from' => null */
    private function bootstrapChanges(array $payload): array
    {
        $changes = [];
        foreach ($this->diffKeys as $k) {
            if(array_key_exists($k, $payload)) {
                $changes[$k] = ['from' => null, 'to' => $payload[$k]];
            }
        }
        return $changes;
    }

    public function start(string $message = 'Starting...'): void 
    {
        $this->write([
            'phase' => 'starting',
            'processed' => 0,
            'total' => null,
            'percent' => 0,
            'last_range' => null,
            'message' => $message,
            'is_running' => true,
        ]);
    }

    public function phase(string $phase, int $processed, ?int $total, ?string $lastRange = null, ?string $message = null): void
    {
        $percent = $total ? (int) floor($processed / max(1, $total) * 100) : null;
        $this->write([
            'phase' => $phase,
            'processed' => $processed,
            'total' => $total,
            'percent' => $percent,
            'last_range' => $lastRange,
            'message' => $message,
            'is_running' => true,
        ]);
    }

    public function done(?int $processed = null, ?int $total = null, ?string $message = 'Done'): void 
    {
        $this->write([
            'phase' => 'done',
            'processed' => $processed ?? 0,
            'total' => $total,
            'percent' => 100,
            'last_range' => null,
            'message' => $message,
            'is_running' => false,
        ]);
    }

    public function error(string $message): void
    {
        $this->write([
            'phase' => 'error',
            'processed' => 0,
            'total' => null,
            'percent' => 0,
            'last_range' => null,
            'message' => $message,
            'is_running' => false,
        ]);
    }
}
