<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

class AuditLogPage extends Component
{
    use WithPagination;

    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        abort_unless(auth()->user()?->hasRole('super-admin'), 403);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Activity::with(['causer', 'subject'])->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('event', 'like', '%' . $this->search . '%')
                  ->orWhereHasMorph('causer', '*', function ($causerQuery) {
                      // Attempt to search by name on any causer type (usually User)
                      $causerQuery->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhere('subject_type', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.audit-log-page', [
            'activities' => $query->paginate(20)
        ])->layout('new.layouts.app');
    }
}
