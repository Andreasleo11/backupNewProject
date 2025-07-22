<?php

namespace App\Livewire;

use App\Models\DeliveryNote;
use Livewire\Component;

class DeliveryNoteShow extends Component
{
    public DeliveryNote $deliveryNote;

    public function mount($id)
    {
        $this->deliveryNote = DeliveryNote::with('destinations')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.delivery-note-show');
    }
}
