<?php

namespace App\Livewire\Consumables;

use App\Models\ConsumableCategory;
use Livewire\Component;
use Livewire\WithPagination;

class ConsumableCategoryManager extends Component
{
    use WithPagination;

    public $search = '';
    public $name;
    public $editingCategoryId = null;

    protected $updatesQueryString = ['search'];

    public function render()
    {
        $categories = ConsumableCategory::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.consumables.consumable-category-manager', [
            'categories' => $categories,
        ]);
    }

    public function resetFields()
    {
        $this->name = '';
        $this->editingCategoryId = null;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:consumable_categories,name,' . $this->editingCategoryId,
        ]);

        ConsumableCategory::updateOrCreate(
            ['id' => $this->editingCategoryId],
            ['name' => $this->name]
        );

        session()->flash('message', $this->editingCategoryId ? 'Category updated successfully.' : 'Category created successfully.');
        $this->resetFields();
    }

    public function edit($id)
    {
        $category = ConsumableCategory::findOrFail($id);
        $this->editingCategoryId = $id;
        $this->name = $category->name;
    }

    public function delete($id)
    {
        ConsumableCategory::find($id)->delete();
        session()->flash('message', 'Category deleted successfully.');
    }
}
