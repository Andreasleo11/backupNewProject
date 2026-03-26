<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Application\Dashboard\DashboardService;
use Livewire\Component;

class ApprovalQueue extends Component
{
    public function render(DashboardService $dashboardService)
    {
        return view('livewire.dashboard.widgets.approval-queue', [
            'approvals' => $dashboardService->getPendingApprovals()
        ]);
    }
}
