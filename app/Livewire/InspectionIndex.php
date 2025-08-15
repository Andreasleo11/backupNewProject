<?php

namespace App\Livewire;

use App\Models\InspectionForm\InspectionReport;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InspectionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Bind to query string ?s=... (same as your controller)
    #[Url(as: 's', except: '')]
    public string $search = '';

    // Reset to page 1 when search changes
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $reports = InspectionReport::query()
            ->when($this->search, function ($q) {
                $q->where('document_number', 'like', "%{$this->search}%")
                    ->orWhere('customer', 'like', "%{$this->search}%")
                    ->orWhere('part_number', 'like', "%{$this->search}%");
            })
            ->latest('inspection_date')
            ->paginate(10);

        return view('livewire.inspection-form.index', [
            'reports' => $reports,
        ])->layout('layouts.guest');
    }
}
