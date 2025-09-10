<?php

namespace App\Jobs;

use App\Models\ImportJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FinalizeImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $jobId)
    {
        // $this->onQueue('imports');
        $this->timeout = 120;
        $this->tries = 1;
    }

    public function handle(): void
    {
        $job = ImportJob::find($this->jobId);
        if (!$job || $job->status === "failed") {
            return;
        }

        $total = $job->total_rows ?: $job->processed_rows;
        $job->update([
            "status" => "completed",
            "total_rows" => $total,
            "finished_at" => now(),
        ]);
    }
}
