<?php

namespace App\Imports;

use App\Models\ImportJob;
use App\Models\MasterDataPart;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Throwable;

class MasterDataPartsImportQueued implements ShouldQueue, ToCollection, WithBatchInserts, WithChunkReading, WithHeadingRow
{
    use Queueable;

    public function __construct(protected int $jobId)
    {
        // Set queue & runtime options WITHOUT redeclaring properties.
        // $this->onQueue('imports');  // uses trait's $queue
        $this->timeout = 1200; // uses trait's $timeout
        $this->tries = 1; // uses trait's $tries
    }

    public function collection(Collection $rows): void
    {
        $job = ImportJob::find($this->jobId);

        // Mark running on first chunk
        if ($job && $job->status === 'pending') {
            $job->update([
                'status' => 'running',
                'started_at' => now(),
            ]);
        }

        try {
            $payload = [];
            $skipped = 0;

            // Build clean rows; collect reasons for skips
            foreach ($rows as $row) {
                $itemNo = trim((string) ($row['item_no'] ?? ''));
                $desc = trim((string) ($row['item_description'] ?? ''));
                $group = $row['item_group'] ?? null;
                $activeR = (string) ($row['active'] ?? '');

                // Minimal validation
                $reason = null;
                if ($itemNo === '') {
                    $reason = 'item_no empty';
                } elseif ($desc === '') {
                    $reason = 'description empty';
                } elseif (! is_numeric($group)) {
                    $reason = 'item_group not numeric';
                }

                if ($reason) {
                    $skipped++;
                    $this->logSkip($job, [
                        'item_no' => $itemNo,
                        'item_desc' => $desc,
                        'item_group_raw' => is_null($group) ? '' : (string) $group,
                        'active_raw' => $activeR,
                        'reason' => $reason,
                    ]);

                    continue;
                }

                $payload[] = [
                    'item_no' => $itemNo,
                    'description' => $desc,
                    'item_group' => (int) $group,
                    'active' => $this->toBool01($activeR),
                ];
            }

            // Determine created vs updated for this chunk (before upsert)
            $createdCount = 0;
            $updatedCount = 0;

            if ($payload) {
                $itemNos = array_column($payload, 'item_no');
                $existingNos = MasterDataPart::whereIn('item_no', $itemNos)
                    ->pluck('item_no')
                    ->all();
                $existingIndex = array_flip($existingNos);

                foreach ($payload as $p) {
                    isset($existingIndex[$p['item_no']]) ? $updatedCount++ : $createdCount++;
                }

                // Upsert in sub-chunks for memory safety
                foreach (array_chunk($payload, 1000) as $chunk) {
                    MasterDataPart::upsert(
                        $chunk,
                        ['item_no'],
                        ['description', 'item_group', 'active'],
                    );
                }
            }

            // Update progress + counters atomically
            if ($job) {
                DB::table('import_jobs')
                    ->where('id', $job->id)
                    ->update([
                        'processed_rows' => DB::raw('processed_rows + '.(int) $rows->count()),
                        'created_rows' => DB::raw('created_rows + '.(int) $createdCount),
                        'updated_rows' => DB::raw('updated_rows + '.(int) $updatedCount),
                        'skipped_rows' => DB::raw('skipped_rows + '.(int) $skipped),
                        'updated_at' => now(),
                    ]);
            }
        } catch (Throwable $e) {
            if ($job) {
                // Add a one-liner to the same log file for post-mortem
                Storage::disk('local')->append(
                    "import_logs/job-{$job->id}.csv",
                    '"","","","","EXCEPTION: '.str_replace('"', '""', $e->getMessage())."\"\n",
                );
                $job->update([
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'finished_at' => now(),
                ]);
            }
            throw $e;
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    /** Convert Active column to 0/1 with robust mapping */
    private function toBool01(string $raw): int
    {
        $v = strtoupper(trim($raw));

        return in_array($v, ['Y', 'YES', '1', 'TRUE', 'ACTIVE', 'AKTIF'], true) ? 1 : 0;
    }

    /** Append a skipped-row line to a per-job CSV log and ensure job has link to it */
    private function logSkip(?ImportJob $job, array $data): void
    {
        if (! $job) {
            return;
        }

        $dir = 'import_logs';
        $file = "job-{$job->id}.csv";
        $path = $dir.'/'.$file;

        // Create header if file not exists
        if (! Storage::disk('local')->exists($path)) {
            Storage::disk('local')->makeDirectory($dir);
            Storage::disk('local')->put(
                $path,
                "item_no,item_description,item_group_raw,active_raw,reason\n",
            );
            // Save link once
            if (! $job->error_log_path) {
                $job->update(['error_log_path' => $path]);
            }
        }

        // CSV-escape double quotes
        $line = sprintf(
            "\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
            str_replace('"', '""', $data['item_no'] ?? ''),
            str_replace('"', '""', $data['item_desc'] ?? ''),
            str_replace('"', '""', $data['item_group_raw'] ?? ''),
            str_replace('"', '""', $data['active_raw'] ?? ''),
            str_replace('"', '""', $data['reason'] ?? ''),
        );

        Storage::disk('local')->append($path, $line);
    }

    /** Mark job failed if the queue job crashes outside collection() */
    public function failed(Throwable $e): void
    {
        if ($job = ImportJob::find($this->jobId)) {
            $job->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }
    }
}
