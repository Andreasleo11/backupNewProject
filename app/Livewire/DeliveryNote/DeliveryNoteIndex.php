<?php

namespace App\Livewire\DeliveryNote;

use App\Models\DeliveryNote;
use Livewire\Component;
use Livewire\WithPagination;

class DeliveryNoteIndex extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $inputStatus = 'all';
    public $inputBranch = 'all';
    public $inputRitasi = 'all';
    public $inputFromDate;
    public $inputToDate;
    public $inputDriver = '';
    public $inputVehicle = '';

    public $filterStatus = 'all';
    public $filterBranch = 'all';
    public $filterRitasi = 'all';
    public $fromDate;
    public $toDate;
    public $searchDriver = '';
    public $searchVehicle = '';

    public function updating($name)
    {
        if (in_array($name, ['filterStatus', 'filterBranch', 'filterRitasi', 'fromDate', 'toDate', 'searchDriver', 'searchVehicle'])) {
            dd("Updaing: $name");
            $this->resetPage();
        }
    }

    public function setFilter($status)
    {
        $this->filterStatus = $status;
    }

    public function applyFilters()
    {
        $this->filterStatus = $this->inputStatus;
        $this->filterBranch = $this->inputBranch;
        $this->filterRitasi = $this->inputRitasi;
        $this->fromDate = $this->inputFromDate;
        $this->toDate = $this->inputToDate;
        $this->searchDriver = $this->inputDriver;
        $this->searchVehicle = $this->inputVehicle;

        $this->resetPage();
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

        if ($this->searchDriver) {
            $query->where('driver_name', 'like', '%' . $this->searchDriver . '%');
        }

        if ($this->searchVehicle) {
            $query->where('vehicle_number', 'like', '%' . $this->searchVehicle . '%');
        }

        return view('livewire.delivery-note.index', [
            'deliveryNotes' => $query->latest()->paginate(10),
        ]);
    }
}
