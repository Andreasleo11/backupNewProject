<?php

namespace App\Livewire\Vehicles;

use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Vehicle $vehicle;

    public string $year = 'all';

    public string $workshop = '';

    public int $perPage = 20;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle->load([
            'latestService' => fn ($q) => $q->with('items'),
        ]);
    }

    public function render()
    {
        $base = ServiceRecord::query()
            ->with('items')
            ->where('vehicle_id', $this->vehicle->id);

        $records = (clone $base)
            ->when($this->year !== 'all', fn ($q) => $q->whereYear('service_date', $this->year))
            ->when($this->workshop !== '', fn ($q) => $q->where('workshop', 'like', '%'.$this->workshop.'%'))
            ->orderByDesc('service_date')
            ->paginate($this->perPage);

        $lifetimeCost = (clone $base)->sum('total_cost');
        $ytdCost = (clone $base)->whereYear('service_date', now()->year)->sum('total_cost');

        return view('livewire.vehicles.show', compact('records', 'lifetimeCost', 'ytdCost'));
    }
}
