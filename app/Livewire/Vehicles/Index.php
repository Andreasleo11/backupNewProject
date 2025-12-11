<?php

namespace App\Livewire\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Illuminate\Database\QueryException;
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

    public $fullFeature = false;

    public function mount()
    {
        $this->fullFeature = auth()->user()->hasRole('super-admin') || (auth()->user()->department->name === 'PERSONALIA');
    }

    public function sortBy(string $field): void
    {
        $allowed = $this->fullFeature ? ['plate_number', 'driver_name', 'odometer', 'status', 'last_service_date'] : ['plate_number', 'driver_name'];

        if (! in_array($field, $allowed, true)) {
            return; // ignore disallowed sorts
        }

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

    public function deleteVehicle(int $id): void
    {
        // Only allow delete for "full feature" users
        if (! $this->fullFeature) {
            abort(403);
        }

        $vehicle = Vehicle::findOrFail($id);

        try {
            $vehicle->delete(); // Soft delete if your model uses SoftDeletes
            session()->flash('success', 'Vehicle deleted.');
        } catch (QueryException $e) {
            // (Optional) Handle FK constraint or other DB issues gracefully
            session()->flash('error', 'Unable to delete this vehicle (it may have related records).');
        }

        // Reset pagination so you don’t land on an empty page after deletion
        $this->resetPage();
    }

    public function render()
    {
        // Ensure sort field is allowed for this role
        $allowed = $this->fullFeature ? ['plate_number', 'driver_name', 'odometer', 'status', 'last_service_date'] : ['plate_number', 'driver_name'];

        $sortField = in_array($this->sort, $allowed, true) ? $this->sort : 'plate_number';
        $sortDir = $this->dir === 'desc' ? 'desc' : 'asc';

        $query = Vehicle::query()->select('vehicles.*');

        if ($this->fullFeature) {
            $query
                ->selectSub(ServiceRecord::select('service_date')->whereColumn('vehicle_id', 'vehicles.id')->orderByDesc('service_date')->limit(1), 'last_service_date')
                ->selectSub(ServiceRecord::select('odometer')->whereColumn('vehicle_id', 'vehicles.id')->orderByDesc('service_date')->limit(1), 'last_service_odometer')
                ->with([
                    'latestService' => fn ($q) => $q->withCount('items'),
                    'latestService.items' => fn ($q) => $q->limit(5),
                ]);
        }

        $query
            ->when(
                $this->q,
                fn ($q) => $q->where(function ($w) {
                    $w->where('plate_number', 'like', '%'.$this->q.'%')
                        ->orWhere('brand', 'like', '%'.$this->q.'%')
                        ->orWhere('model', 'like', '%'.$this->q.'%')
                        ->orWhere('driver_name', 'like', '%'.$this->q.'%');
                }),
            )
            ->when($this->fullFeature && $this->status !== 'all', function ($q) {
                $q->where('status', VehicleStatus::from($this->status));
            })
            ->orderBy($sortField, $sortDir);

        return view('livewire.vehicles.index', [
            'vehicles' => $query->paginate($this->perPage),
            'fullFeature' => $this->fullFeature,
        ]);
    }
}
