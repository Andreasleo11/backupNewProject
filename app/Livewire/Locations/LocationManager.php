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

    protected $updatesQueryString = ['search'];

    public function render()
    {
        $locations = AssetLocation::query()
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
    }

    public function delete($id)
    {
        AssetLocation::find($id)->delete();
        session()->flash('message', 'Location deleted successfully.');
    }
}
