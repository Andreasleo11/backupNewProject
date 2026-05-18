<?php

namespace App\Livewire\Assets;

use App\Models\AssetCategory;
use Livewire\Component;
use Livewire\WithPagination;

class AssetCategoryManager extends Component
{
    use WithPagination;

    public $search = '';
    public $name;
    public $editingCategoryId = null;
    public $showForm = false;

    protected $updatesQueryString = ['search'];

    public function render()
    {
        $categories = AssetCategory::withCount('assets')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.assets.asset-category-manager', [
            'categories' => $categories,
        ]);
    }

    public function resetFields()
    {
        $this->name = '';
        $this->editingCategoryId = null;
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
            'name' => 'required|string|max:255|unique:asset_categories,name,' . $this->editingCategoryId,
        ]);

        AssetCategory::updateOrCreate(
            ['id' => $this->editingCategoryId],
            ['name' => $this->name]
        );

        session()->flash('message', $this->editingCategoryId ? 'Category updated successfully.' : 'Category created successfully.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $category = AssetCategory::findOrFail($id);
        $this->editingCategoryId = $id;
        $this->name = $category->name;
        $this->showForm = true;
    }

    public function delete($id)
    {
        AssetCategory::find($id)->delete();
        session()->flash('message', 'Category deleted successfully.');
    }
}
