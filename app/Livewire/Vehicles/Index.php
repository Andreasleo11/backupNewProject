<?php

namespace App\Livewire\Vehicles;

use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url(as: 'st')]
    public string $status = 'all';

    #[Url(as: 'pp')]
    public int $perPage = 10;

    #[Url(as: 'sort')]
    public string $sort = 'plate_number';

    #[Url(as: 'dir')]
    public string $dir = 'asc';

    public function sortBy(string $field): void
    {
        if ($this->sort === $field) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->dir = 'asc';
        }
        $this->resetPage();
    }

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Vehicle::query()
            ->select('vehicles.*')
            ->selectSub(
                ServiceRecord::select('service_date')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->orderByDesc('service_date')
                    ->limit(1),
                'last_service_date'
            )
            ->selectSub(
                ServiceRecord::select('odometer')
                    ->whereColumn('vehicle_id', 'vehicles.id')
                    ->orderByDesc('service_date')
                    ->limit(1),
                'last_service_odometer'
            )
            ->with([
                'latestService' => fn ($q) => $q->withCount('items'),
                'latestService.items' => fn ($q) => $q->limit(5),
            ])
            ->when($this->q, fn ($q) => $q->where(function ($w) {
                $w->where('plate_number', 'like', '%'.$this->q.'%')
                    ->orWhere('brand', 'like', '%'.$this->q.'%')
                    ->orWhere('model', 'like', '%'.$this->q.'%')
                    ->orWhere('driver_name', 'like', '%'.$this->q.'%');
            }))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->orderBy($this->sort, $this->dir);

        return view('livewire.vehicles.index', ['vehicles' => $query->paginate($this->perPage)]);
    }
}
