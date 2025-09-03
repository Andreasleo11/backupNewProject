<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class SyncProgress extends Component
{
    public string $companyArea = '10000';
    public array $data = [];
    public array $events = [];

    public bool $compact = true;

    public function mount(String $companyArea = '10000'): void 
    {
        // Cache::forget("sync_progress_10000");
        // Cache::forget("sync_progress_events_10000");
        $this->companyArea = $companyArea;
        $this->refreshProgress();
        $this->compact = (bool) session('sync_progress.compact', true);
    }

    public function refreshProgress(): void
    {
        $this->data = Cache::get("sync_progress_{$this->companyArea}", []);
        $this->events = Cache::get("sync_progress_events_{$this->companyArea}", []);
    }

    public function toggleDetail(): void
    {
        // if (optional(auth()->user()->role)->name !== 'SUPERADMIN') return;
        $this->compact = !$this->compact;
        session(['sync_progress.compact' => $this->compact]);
    }

    public function render()
    {
        return view('livewire.sync-progress');
    }
}
