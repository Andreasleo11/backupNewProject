<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Application\Dashboard\DashboardService;
use Livewire\Component;

class ActivityTimeline extends Component
{
    public function render(DashboardService $dashboardService)
    {
        return view('livewire.dashboard.widgets.activity-timeline', [
            'activities' => $dashboardService->getRecentActivities(),
        ]);
    }
}
