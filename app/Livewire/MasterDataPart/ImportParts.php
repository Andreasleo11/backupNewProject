<?php

namespace App\Livewire\MasterDataPart;

use App\Imports\MasterDataPartsImportQueued;
use App\Jobs\FinalizeImportJob;
use App\Models\ImportJob;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ImportParts extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:xlsx,xls,csv|max:51200')]
    public $file;

    #[Url(as: 'job')]   // ?job=123 will persist
    public ?int $jobId = null;

    #[On('track-job')]
    public function trackJob(int $id): void
    {
        $this->jobId = $id; // URL param updates too (because of #[Url(as:'job')])
    }

    public function mount()
    {
        if (!$this->jobId) {
            $running = \App\Models\ImportJob::whereIn('status', ['pending', 'running'])
                ->latest('id')->first();
            if ($running) $this->jobId = $running->id;
        }
    }

    public function import()
    {
        $this->validate();

        // 1) Persist upload (never pass UploadedFile to the queue)
        $ext  = $this->file->getClientOriginalExtension() ?: 'xlsx';
        $name = 'parts-' . now()->format('Ymd-His') . '-' . Str::uuid() . '.' . $ext;
        $path = $this->file->storeAs('imports', $name, 'local'); // storage/app/imports/...

        // 2) Pre-scan rows from stored file (sync)
        $counter = new class implements ToCollection, WithHeadingRow {
            public int $count = 0;
            public function collection(Collection $rows): void
            {
                $this->count = $rows->count();
            }
        };
        Excel::import($counter, $path, 'local');
        Excel::clearResolvedInstances();
        $totalRows = max($counter->count, 0);

        // 3) Create job record
        $job = ImportJob::create([
            'type'           => 'master_data_parts',
            'total_rows'     => $totalRows,
            'processed_rows' => 0,
            'status'         => 'pending',
            'source_disk' => 'local',
            'source_path' => $path,
        ]);
        $this->jobId = $job->id;

        // 4) Queue import by path+disk, then chain a finalizer job
        Excel::queueImport(new MasterDataPartsImportQueued($job->id), $path, 'local')
            // ->allOnQueue('imports')     // <— choose a queue name
            ->chain([
                new FinalizeImportJob($job->id),
            ]);

        $this->reset('file');
    }

    public function getJobProperty(): ?ImportJob
    {
        return $this->jobId ? ImportJob::find($this->jobId) : null;
    }

    public function refreshNow(): void
    {
        // If not tracking, auto-attach to latest running job
        if (!$this->jobId) {
            $running = \App\Models\ImportJob::whereIn('status', ['pending', 'running'])
                ->latest('id')->first();
            if ($running) {
                $this->jobId = $running->id;
            }
        }

        // If tracking, make sure we have the freshest data for this render
        // (getJobProperty() already refetches, so this is optional)
        if ($this->job) {
            $this->job->refresh();
        }
    }

    public function resetTracking(): void
    {
        // Clear the tracked job (URL param will be removed automatically)
        $this->jobId = null;

        // Optional: clear any validation state/toast the user
        $this->resetValidation();

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Tracking cleared. The background import (if any) continues running.',
        ]);
    }



    public function render()
    {
        return view('livewire.master-data-part.import-parts');
    }
}
