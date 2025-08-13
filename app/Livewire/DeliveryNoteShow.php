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

        $latestId = DeliveryNote::max('id');
        $this->deliveryNote->is_latest = $this->deliveryNote->id === $latestId;
    }

    public function render()
    {
        if (!auth()->user()) {
            return view('livewire.delivery-note.show')
                ->layout('layouts.guest');
        }
        return view('livewire.delivery-note.show');
    }
}
