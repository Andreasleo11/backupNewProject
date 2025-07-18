<?php

namespace App\Livewire\DeliveryNote;

use App\Models\DeliveryNote;
use Livewire\Component;

class Index extends Component
{
    public $filterStatus = 'all';

    public function setFilter($status)
    {
        $this->filterStatus = $status;
    }

    public function render()
    {
        $query = DeliveryNote::query()->with('destinations');

        if ($this->filterStatus === 'draft') {
            $query->where('status', 'draft');
        } elseif ($this->filterStatus === 'submitted') {
            $query->where('status', 'submitted');
        }

        return view('livewire.delivery-note.index', [
            'deliveryNotes' => $query->latest()->paginate(10),
        ]);
    }
}
