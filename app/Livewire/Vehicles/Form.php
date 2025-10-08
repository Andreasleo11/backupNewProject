<?php

namespace App\Livewire\Vehicles;

use App\Enums\VehicleStatus;
use App\Models\Vehicle;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Vehicle $vehicle = null;

    public string $driver_name = '';

    public string $plate_number = '';

    public ?string $brand = null;

    public ?string $model = null;

    public ?int $year = null;

    public ?string $vin = null;

    public int $odometer = 0;

    public string $status = 'active';

    public bool $fullFeature = false;

    public ?string $sold_at = null;

    protected function rules(): array
    {
        // Non-FULLFEATURE can only edit these two
        if (! $this->fullFeature) {
            return [
                'driver_name' => ['nullable', 'string', 'max:255'],
                'plate_number' => [
                    'required', 'string', 'max:20',
                    Rule::unique('vehicles', 'plate_number')
                        ->ignore($this->vehicle?->id)
                        ->whereNull('deleted_at'),
                ],
            ];
        }

        // full rules
        return [
            'driver_name' => ['nullable', 'string', 'max:255'],
            'plate_number' => [
                'required', 'string', 'max:20',
                Rule::unique('vehicles', 'plate_number')
                    ->ignore($this->vehicle?->id)
                    ->whereNull('deleted_at'),
            ],
            'brand' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:120'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'vin' => ['nullable', 'string', 'max:50'],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_column(VehicleStatus::cases(), 'value'))],
            'sold_at' => ['nullable', 'date', 'before_or_equal:today', 'required_if:status,sold'],
        ];
    }

    public function mount(?Vehicle $vehicle): void
    {
        $this->fullFeature = auth()->user()?->role->name === 'SUPERADMIN' || (auth()->user()->department->name === 'PERSONALIA');

        if ($vehicle?->exists) {
            $this->vehicle = $vehicle;

            // Fill only the fields allowed for this role
            $fields = $this->fullFeature
                ? ['driver_name', 'plate_number', 'brand', 'model', 'year', 'vin', 'odometer', 'status', 'sold_at']
                : ['driver_name', 'plate_number'];

            $data = Arr::only($vehicle->toArray(), $fields);

            $this->fill($data);
        }
    }

    public function save()
    {
        $this->validate();

        $payload = [
            'driver_name' => $this->driver_name,
            'plate_number' => $this->plate_number,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year ?: null,
            'vin' => $this->vin ?: null,
            'odometer' => (int) ($this->odometer ?: 0),
            'status' => $this->status,
            'sold_at' => $this->status === 'sold' ? $this->sold_at ?? now()->toDateString() : null,
        ];

        $allowedKeys = $this->fullFeature
            ? array_keys($payload)
            : ['driver_name', 'plate_number']; // hard guard

        // Keep only allowed keys
        $data = array_intersect_key($payload, array_flip($allowedKeys));

        // Optionally enforce a safe default for non-fullfeature creates
        if (! $this->fullFeature && ! $this->vehicle?->exists) {
            $data['status'] = 'active'; // only if your DB column is NOT NULL
        }

        if ($this->vehicle?->exists) {
            $this->vehicle->update($data);
            session()->flash('success', 'Vehicle updated.');
        } else {
            $this->vehicle = Vehicle::create($data);
            session()->flash('success', 'Vehicle created.');
        }

        if (! $this->fullFeature) {
            return redirect()->route('vehicles.index');
        }

        return redirect()->route('vehicles.show', $this->vehicle);
    }

    public function delete(): void
    {
        // Only ALLOWED USER can delete
        if (! $this->fullFeature) {
            abort(403);
        }

        if ($this->vehicle?->exists) {
            $this->vehicle->delete();
            session()->flash('success', 'Vehicle deleted.');
            $this->redirectRoute('vehicles.index', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.vehicles.form', [
            'fullFeature' => $this->fullFeature,
        ]);
    }
}
