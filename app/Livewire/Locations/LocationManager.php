<?php

namespace App\Livewire\Locations;

use App\Models\AssetLocation;
use Livewire\Component;
use Livewire\WithPagination;

class LocationManager extends Component
{
    use WithPagination;

    public $search = '';
    public $name;
    public $editingLocationId = null;
    public $showForm = false;

    protected $updatesQueryString = ['search'];

    public function render()
    {
        $locations = AssetLocation::withCount('assets')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.locations.location-manager', [
            'locations' => $locations,
        ]);
    }

    public function resetFields()
    {
        $this->name = '';
        $this->editingLocationId = null;
        $this->showForm = false;
    }

    public function showAddForm()
    {
        $this->resetFields();
        $this->showForm = true;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:asset_locations,name,' . $this->editingLocationId,
        ]);

        AssetLocation::updateOrCreate(
            ['id' => $this->editingLocationId],
            ['name' => $this->name]
        );

        session()->flash('message', $this->editingLocationId ? 'Location updated successfully.' : 'Location created successfully.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $location = AssetLocation::findOrFail($id);
        $this->editingLocationId = $id;
        $this->name = $location->name;
        $this->showForm = true;
    }

    public function delete($id)
    {
        AssetLocation::find($id)->delete();
        session()->flash('message', 'Location deleted successfully.');
    }
}
