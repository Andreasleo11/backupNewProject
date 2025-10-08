<?php

namespace App\Livewire\Vehicles;

use App\Models\ServiceRecord;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Vehicle $vehicle;

    public string $year = 'all';

    public string $workshop = '';

    public int $perPage = 20;

    public bool $canManage = false;

    public function mount(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle->load([
            'latestService' => fn ($q) => $q->with('items'),
        ]);

        // Mirror your index role logic
        $user = auth()->user();
        $this->canManage = $user->role->name === 'SUPERADMIN'
            || ($user->is_head && $user->department->name === 'PERSONALIA');
    }

    public function deleteService(int $id): void
    {
        if (! $this->canManage) {
            abort(403);
        }

        $record = ServiceRecord::where('vehicle_id', $this->vehicle->id)->findOrFail($id);

        try {
            DB::transaction(function () use ($record) {
                // If you don't have DB cascades, uncomment the next line:
                // $record->items()->delete();

                $record->delete();
            });

            session()->flash('success', 'Service record deleted.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to delete service record.');
        }

        // Keep pagination sane after a delete
        $this->resetPage();

        // Refresh header stats (latest service, etc.)
        $this->vehicle = $this->vehicle->fresh()->load([
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
