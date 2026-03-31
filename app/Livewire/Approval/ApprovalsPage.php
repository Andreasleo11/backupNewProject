<?php

namespace App\Livewire\Approval;

use App\Application\Dashboard\DashboardService;
use Livewire\Component;
use Livewire\WithPagination;

class ApprovalsPage extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public ?int $selectedId = null;
    public ?string $selectedType = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
    ];

    public function openQuickView($id, $type)
    {
        $this->selectedId = $id;
        $this->selectedType = $type;
        $this->dispatch('open-quick-view-modal');
    }

    public function closeQuickView()
    {
        $this->selectedId = null;
        $this->selectedType = null;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render(DashboardService $dashboardService)
    {
        $query = $dashboardService->getPendingApprovalsQuery();
        
        // Eager load total steps count to show "Level X of Y"
        $query->with(['request' => function($q) {
            $q->withCount('steps');
        }]);

        if ($this->search) {
            $query->whereHas('request.approvable', function ($q) {
                // Search in common fields across approvables
                $q->where('id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->whereHas('request', function ($q) {
                $q->where('approvable_type', 'like', '%' . $this->filterType . '%');
            });
        }

        return view('livewire.approval.approvals-page', [
            'approvals' => $query->paginate(10)
        ])->layout('new.layouts.app');
    }
}
