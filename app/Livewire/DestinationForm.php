<?php

namespace App\Livewire;

use App\Models\Destination;
use Livewire\Component;

class DestinationForm extends Component
{
    public $destinationId;

    public $name;

    public $city;

    public $description;

    public function mount($id = null)
    {
        if ($id) {
            $dest = Destination::findOrFail($id);
            $this->destinationId = $dest->id;
            $this->name = $dest->name;
            $this->city = $dest->city;
            $this->description = $dest->description;
        }
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function save()
    {
        $data = $this->validate();

        Destination::updateOrCreate(['id' => $this->destinationId], $data);

        session()->flash('success', 'Destination saved successfully.');

        return redirect()->route('destination.index');
    }

    public function render()
    {
        return view('livewire.destination-form');
    }
}
