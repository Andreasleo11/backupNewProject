<?php

namespace App\Livewire\DeliveryNote;

use App\Models\DeliveryNote;
use Livewire\Component;
use Livewire\WithPagination;

class DeliveryNoteIndex extends Component
{
    use WithPagination;

    public $inputStatus = 'all';

    public $inputBranch = 'all';

    public $inputRitasi = 'all';

    public $inputFromDate;

    public $inputToDate;

    public $filterStatus = 'all';

    public $filterBranch = 'all';

    public $filterRitasi = 'all';

    public $fromDate;

    public $toDate;

    public $searchAll = '';

    public $sortField = 'id';

    public $sortDirection = 'desc';

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function applyFilters()
    {
        $this->filterStatus = $this->inputStatus;
        $this->filterBranch = $this->inputBranch;
        $this->filterRitasi = $this->inputRitasi;
        $this->fromDate = $this->inputFromDate;
        $this->toDate = $this->inputToDate;

        $this->resetPage();
    }

    public function delete($id)
    {
        $note = DeliveryNote::findOrFail($id);
        $note->delete();

        session()->flash('success', 'Delivery Note deleted successfully.');
    }

    public function updatingSearchAll()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = DeliveryNote::query()->with('destinations');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterBranch !== 'all') {
            $query->where('branch', $this->filterBranch);
        }

        if ($this->filterRitasi !== 'all') {
            $query->where('ritasi', $this->filterRitasi);
        }

        if ($this->fromDate) {
            $query->whereDate('delivery_note_date', '>=', $this->fromDate);
        }

        if ($this->fromDate) {
            $query->whereDate('delivery_note_date', '<=', $this->toDate);
        }

        if ($this->searchAll) {
            $search = '%'.$this->searchAll.'%';

            $query->where(function ($q) use ($search) {
                $q->where('branch', 'like', $search)
                    ->orWhere('ritasi', 'like', $search)
                    ->orWhere('status', 'like', $search)
                    ->orWhere('delivery_note_date', 'like', $search)
                    ->orWhereHas('vehicle', function ($v) use ($search) {
                        $v->where('plate_number', 'like', $search)->orWhere(
                            'driver_name',
                            'like',
                            $search,
                        );
                    });
            });
        }

        $deliveryNotes = $query
            ->select('delivery_notes.*')
            ->leftJoin('vehicles', 'vehicles.id', '=', 'delivery_notes.vehicle_id')
            ->with('vehicle');

        if (in_array($this->sortField, ['vehicle_number', 'driver_name'])) {
            $query->orderBy("vehicles.{$this->sortField}", $this->sortDirection);
        } else {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        $latestNoteId = DeliveryNote::max('id');

        $deliveryNotes = $query->paginate(10)->through(function ($note) use ($latestNoteId) {
            $note->latest = $note->id === $latestNoteId;

            return $note;
        });

        if (! auth()->check()) {
            return view('livewire.delivery-note.index', [
                'deliveryNotes' => $deliveryNotes,
            ])->layout('layouts.guest');
        }

        return view('livewire.delivery-note.index', [
            'deliveryNotes' => $deliveryNotes,
        ]);
    }
}
