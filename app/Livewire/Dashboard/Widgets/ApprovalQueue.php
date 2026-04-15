<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Application\Dashboard\DashboardService;
use Livewire\Component;

class ApprovalQueue extends Component
{
    public ?int $selectedId = null;

    public ?string $selectedType = null;

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

    public function render(DashboardService $dashboardService)
    {
        return view('livewire.dashboard.widgets.approval-queue', [
            'approvals' => $dashboardService->getPendingApprovals(),
        ]);
    }
}
