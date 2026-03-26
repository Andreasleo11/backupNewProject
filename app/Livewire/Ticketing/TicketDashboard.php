<?php

namespace App\Livewire\Ticketing;

use App\Domains\Ticketing\Actions\CalculateKpiMetrics;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class TicketDashboard extends Component
{
    public array $metrics = [];

    public function mount(CalculateKpiMetrics $calculator)
    {
        // For MVP: Show KPI metrics for the currently logged-in IT Person (PIC)
        // In a full admin panel, we'd pass a selected User object.
        $this->metrics = $calculator->execute(Auth::user());
    }

    #[Layout('new.layouts.app')]
    public function render()
    {
        return view('livewire.ticketing.ticket-dashboard');
    }
}
