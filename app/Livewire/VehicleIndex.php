<?php

namespace App\Livewire;

use App\Models\Vehicle;
use Livewire\Component;

class VehicleIndex extends Component
{
    public $search = "";

    public function delete($id)
    {
        Vehicle::findOrFail($id)->delete();
        session()->flash("success", "Vehicle deleted successfully.");
    }

    public function render()
    {
        $vehicles = Vehicle::where("plate_number", "like", "%" . $this->search . "%")
            ->orWhere("driver_name", "like", "%" . $this->search . "%")
            ->orderBy("plate_number")
            ->get();
        return view("livewire.vehicle-index", compact("vehicles"));
    }
}
