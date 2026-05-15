<?php

namespace App\Livewire\Locker;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Spatie\Activitylog\Models\Activity;

class LockerAuditLog extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function logs()
    {
        return Activity::query()
            ->whereIn('subject_type', ['App\Models\Locker', 'App\Models\LockerAssignment'])
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.locker.locker-audit-log')->layout('new.layouts.app');
    }
}
