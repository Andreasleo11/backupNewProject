<?php

namespace App\Livewire\Dashboard\Widgets;

use Livewire\Component;

class KpiCard extends Component
{
    public string $label;

    public string $value;

    public string $trend = '';

    public string $icon = 'bar-chart';

    public string $color = 'blue';

    public ?string $url = null;

    public function render()
    {
        return view('livewire.dashboard.widgets.kpi-card');
    }
}
