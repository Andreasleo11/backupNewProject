<?php

namespace App\Livewire;

use App\Models\Vehicle;
use Livewire\Component;

class VehicleForm extends Component
{
    public $vehicleId;

    public $plate_number;

    public $driver_name;

    public function mount($id = null)
    {
        if ($id) {
            $v = Vehicle::findOrFail($id);
            $this->vehicleId = $id;
            $this->plate_number = $v->plate_number;
            $this->driver_name = $v->driver_name;
        }
    }

    public function rules()
    {
        return [
            'plate_number' => 'required|string|max:255|unique:vehicles,plate_number,'.$this->vehicleId,
            'driver_name' => 'required|string|max:255',
        ];
    }

    public function save()
    {
        $data = $this->validate();

        Vehicle::updateOrCreate(['id' => $this->vehicleId], $data);

        session()->flash('success', 'Vehicle saved successfully.');

        return redirect()->route('vehicles.index');
    }

    public function render()
    {
        return view('livewire.vehicle-form');
    }
}
