<?php

namespace App\Livewire\MasterDataPart;

use App\Models\ImportJob;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ImportJobsList extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap'; // prevents template swaps

    #[Url(as: 's')]
    public string $search = '';

    #[Url(as: 'st')]
    public string $status = 'all';

    #[Url(as: 'pp')]
    public int $perPage = 5;

    public ?int $selectedJobId = null;

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingStatus()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function getShouldPollProperty(): bool
    {
        // If you prefer to poll only when user is on "All" or "Running" filter:
        return in_array($this->status, ['all', 'running'], true)
            && ImportJob::where('status', 'running')->exists();
    }

    public function selectJob(int $id): void
    {
        $this->selectedJobId = $id;
        // Tell the parent to track this job
        $this->dispatch('track-job', id: $id);
    }

    public function render()
    {
        $q = ImportJob::query()->orderByDesc('id');

        if ($this->status !== 'all') {
            $q->where('status', $this->status);
        }

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $q->where(function ($x) use ($term) {
                $x->where('id', 'like', $term)
                    ->orWhere('type', 'like', $term)
                    ->orWhere('error', 'like', $term);
            });
        }

        $jobs = $q->paginate($this->perPage)->withQueryString();

        return view('livewire.master-data-part.import-jobs-list', compact('jobs'));
    }
}
