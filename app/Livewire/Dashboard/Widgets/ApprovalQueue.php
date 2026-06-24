<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Application\Dashboard\DashboardService;
use Livewire\Attributes\Computed;
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

    #[Computed]
    public function approvals()
    {
        return app(DashboardService::class)->getPendingApprovals(auth()->user());
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.approval-queue');
    }
}
