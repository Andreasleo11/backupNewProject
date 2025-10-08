<?php

namespace App\Livewire\DeliveryNote;

use App\Models\DeliveryNote;
use Livewire\Component;

class DeliveryNotePrint extends Component
{
    public DeliveryNote $deliveryNote;

    public function mount(DeliveryNote $deliveryNote)
    {
        $this->deliveryNote = $deliveryNote->load('destinations.deliveryOrders');
    }

    public function render()
    {
        return view('livewire.delivery-note.print-view')->layout('layouts.print');
    }
}
