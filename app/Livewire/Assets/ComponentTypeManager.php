<?php

namespace App\Livewire\Assets;

use App\Models\ComponentType;
use Livewire\Component;
use Livewire\WithPagination;

class ComponentTypeManager extends Component
{
    use WithPagination;

    public $search = '';
    public $name;
    public $category = 'hardware';
    public $editingComponentTypeId = null;
    public $showForm = false;

    protected $updatesQueryString = ['search'];

    public function render()
    {
        $types = ComponentType::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('category', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.assets.component-type-manager', [
            'types' => $types,
        ]);
    }

    public function resetFields()
    {
        $this->name = '';
        $this->category = 'hardware';
        $this->editingComponentTypeId = null;
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
            'name' => 'required|string|max:255|unique:component_types,name,' . $this->editingComponentTypeId,
            'category' => 'required|in:hardware,software',
        ]);

        ComponentType::updateOrCreate(
            ['id' => $this->editingComponentTypeId],
            [
                'name' => $this->name,
                'category' => $this->category,
            ]
        );

        session()->flash('message', $this->editingComponentTypeId ? 'Component type updated successfully.' : 'Component type created successfully.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $type = ComponentType::findOrFail($id);
        $this->editingComponentTypeId = $id;
        $this->name = $type->name;
        $this->category = $type->category;
        $this->showForm = true;
    }

    public function delete($id)
    {
        ComponentType::findOrFail($id)->delete();
        session()->flash('message', 'Component type deleted successfully.');
    }
}
