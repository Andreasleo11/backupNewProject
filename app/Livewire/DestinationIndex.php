<?php

namespace App\Livewire;

use App\Models\Destination;
use Livewire\Component;

class DestinationIndex extends Component
{
    public $search = '';

    protected $listeners = ['destinationUpdated' => '$refresh'];

    public function delete($id)
    {
        Destination::findOrFail($id)->delete();
        session()->flash('success', 'Destination deleted succesfully.');
    }

    public function render()
    {
        $destinations = Destination::where('name', 'like', '%'.$this->search.'%')->get();

        return view('livewire.destination-index', [
            'destinations' => $destinations,
        ]);
    }
}
