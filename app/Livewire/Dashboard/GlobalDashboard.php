<?php

namespace App\Livewire\Dashboard;

use App\Application\Dashboard\DashboardService;
use Livewire\Component;

class GlobalDashboard extends Component
{
    public array $kpis = [];

    public function mount(DashboardService $dashboardService)
    {
        $this->kpis = $dashboardService->getKpiSummary(auth()->user());
    }

    public function render()
    {
        return view('livewire.dashboard.global-dashboard')
            ->layout('new.layouts.app');
    }
}
