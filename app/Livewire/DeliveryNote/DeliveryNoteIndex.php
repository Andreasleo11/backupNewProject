<?php

namespace App\Livewire\DeliveryNote;

use App\Models\DeliveryNote;
use Livewire\Component;

class DeliveryNoteIndex extends Component
{
    public $filterStatus = 'all';

    public function setFilter($status)
    {
        $this->filterStatus = $status;
    }

    public function delete($id)
    {
        $note = DeliveryNote::findOrFail($id);
        $note->delete();

        session()->flash('success', 'Delivery Note deleted successfully.');
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
