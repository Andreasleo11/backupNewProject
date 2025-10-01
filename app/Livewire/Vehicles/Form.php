<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
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

    public bool $isSuperadmin = false;

    public function mount(?Vehicle $vehicle): void
    {
        $this->isSuperadmin = auth()->user()?->role === 'SUPERADMIN';

        if ($vehicle?->exists) {
            $this->vehicle = $vehicle;

            // Fill only the fields allowed for this role
            $fields = $this->isSuperadmin
                ? ['driver_name', 'plate_number', 'brand', 'model', 'year', 'vin', 'odometer', 'status']
                : ['driver_name', 'plate_number'];

            $this->fill($vehicle->only($fields));
        }
    }

    protected function rules(): array
    {
        // Non-SUPERADMIN can only edit these two
        if (! $this->isSuperadmin) {
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

        // SUPERADMIN full rules
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
            'status' => ['required', 'in:active,maintenance,retired'],
        ];
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
        ];

        $allowedKeys = $this->isSuperadmin
            ? array_keys($payload)
            : ['driver_name', 'plate_number']; // hard guard

        // Keep only allowed keys
        $data = array_intersect_key($payload, array_flip($allowedKeys));

        // Optionally enforce a safe default for non-superadmin creates
        if (! $this->isSuperadmin && ! $this->vehicle?->exists) {
            $data['status'] = 'active'; // only if your DB column is NOT NULL
        }

        if ($this->vehicle?->exists) {
            $this->vehicle->update($data);
            session()->flash('success', 'Vehicle updated.');
        } else {
            $this->vehicle = Vehicle::create($data);
            session()->flash('success', 'Vehicle created.');
        }

        return redirect()->route('vehicles.show', $this->vehicle);
    }

    public function delete(): void
    {
        // Only SUPERADMIN can delete
        if (! $this->isSuperadmin) {
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
            'isSuperadmin' => $this->isSuperadmin,
        ]);
    }
}
