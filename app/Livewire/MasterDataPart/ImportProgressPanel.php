<?php

namespace App\Livewire\MasterDataPart;

use App\Models\ImportJob;
use Livewire\Component;

class ImportProgressPanel extends Component
{
    public ?int $jobId = null;

    public function getJobProperty(): ?ImportJob
    {
        return $this->jobId ? ImportJob::find($this->jobId) : null;
    }

    public function getProgressProperty(): int
    {
        if (! $this->job) {
            return 0;
        }
        $total = max((int) $this->job->total_rows, 1);
        $done = min((int) $this->job->processed_rows, $total);

        return (int) floor(($done / $total) * 100);
    }

    public function getEtaSecondsProperty(): ?int
    {
        if (! $this->job || ! $this->job->started_at || $this->progress <= 0) {
            return null;
        }
        $elapsed = now()->diffInSeconds($this->job->started_at);
        if ($elapsed <= 0) {
            return null;
        }
        $rate = $this->job->processed_rows / $elapsed; // rows/sec
        if ($rate <= 0) {
            return null;
        }
        $remainingRows = max($this->job->total_rows - $this->job->processed_rows, 0);

        return (int) ceil($remainingRows / $rate);
    }

    public function getHeartbeatAgeSecondsProperty(): ?int
    {
        if (! $this->job || ! $this->job->updated_at) {
            return null;
        }

        return now()->diffInSeconds($this->job->updated_at);
    }

    public function forceFailIfStalled(int $seconds = 120): void
    {
        if (! $this->job) {
            return;
        }
        if (
            $this->job->status === 'running' &&
            $this->heartbeatAgeSeconds !== null &&
            $this->heartbeatAgeSeconds >= $seconds
        ) {
            $this->job->update([
                'status' => 'failed',
                'error' => "Stalled: no progress for {$this->heartbeatAgeSeconds}s (worker likely stopped).",
                'finished_at' => now(),
            ]);
        }
    }

    public function refreshProgress(): void
    {
        // Tell the browser (not Livewire parent) about the latest timestamps
        $this->dispatch(
            'job-heartbeat',
            diff: optional($this->job?->updated_at)->diffForHumans() ?? '—',
            absolute: optional($this->job?->updated_at)->toDateTimeString() ?? '',
        );
    }

    public function render()
    {
        // Safety net: mark completed if work is done
        if ($this->job && $this->job->status === 'running') {
            $total = max($this->job->total_rows, 0);
            if ($total > 0 && $this->job->processed_rows >= $total) {
                $this->job->update([
                    'status' => 'completed',
                    'finished_at' => now(),
                ]);
            }
        }

        return view('livewire.master-data-part.import-progress-panel');
    }
}
