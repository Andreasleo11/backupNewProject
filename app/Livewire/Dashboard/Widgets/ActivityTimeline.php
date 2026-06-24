<?php

namespace App\Livewire\Dashboard\Widgets;

use App\Application\Dashboard\DashboardService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ActivityTimeline extends Component
{
    #[Computed]
    public function activities()
    {
        return app(DashboardService::class)->getRecentActivities(auth()->user());
    }

    public function render()
    {
        return view('livewire.dashboard.widgets.activity-timeline');
    }
}
