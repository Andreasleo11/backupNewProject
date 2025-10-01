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

    public function mount(?Vehicle $vehicle): void
    {
        if ($vehicle?->exists) {
            $this->vehicle = $vehicle;
            $this->fill($vehicle->only('driver_name', 'plate_number', 'brand', 'model', 'year', 'vin', 'odometer', 'status'));
        }
    }

    protected function rules(): array
    {
        return [
            'driver_name' => ['nullable', 'string', 'max:255'],
            'plate_number' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plate_number')->ignore($this->vehicle?->id)->whereNull('deleted_at')],
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

        $data = [
            'driver_name' => $this->driver_name,
            'plate_number' => $this->plate_number,
            'brand' => $this->brand,
            'model' => $this->model,
            'year' => $this->year ?: null,
            'vin' => $this->vin ?: null,
            'odometer' => (int) ($this->odometer ?: 0),
            'status' => $this->status,
        ];

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
        if ($this->vehicle?->exists) {
            $this->vehicle->delete();
            session()->flash('success', 'Vehicle deleted.');
            $this->redirectRoute('vehicles.index', navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.vehicles.form');
    }
}
