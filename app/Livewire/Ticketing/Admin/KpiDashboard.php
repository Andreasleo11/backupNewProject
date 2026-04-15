<?php

namespace App\Livewire\Ticketing\Admin;

use App\Domains\Ticketing\Actions\CalculateKpiMetrics;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

class KpiDashboard extends Component
{
    public array $picMetrics = [];

    public function mount(CalculateKpiMetrics $calculator)
    {
        // For the Admin Dashboard, we calculate metrics for all IT PICs.
        // In a real app, this would be scoped by a role, e.g., Spatie's Role::whereName('IT Support')->get()
        // Here we just grab all users who have tickets assigned to them to demonstrate.

        $pics = User::whereHas('assignedTickets')->get();

        foreach ($pics as $pic) {
            $this->picMetrics[] = $calculator->execute($pic);
        }
    }

    #[Layout('new.layouts.app')]
    public function render()
    {
        return view('livewire.ticketing.admin.kpi-dashboard');
    }
}
