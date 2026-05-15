<?php

namespace App\Livewire\Qaqc;

use App\Models\DefectCategory;
use Livewire\Component;
use Livewire\WithPagination;

class DefectCategoryManager extends Component
{
    use WithPagination;

    public $name;
    public $editingId;
    public $editingName;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'editingName' => 'required|string|max:255',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function store()
    {
        $this->validateOnly('name');

        DefectCategory::create([
            'name' => ucfirst($this->name),
        ]);

        $this->reset('name');
        $this->dispatch('close-modal', 'add-category');
        session()->flash('success', 'Category added successfully!');
    }

    public function edit($id)
    {
        $category = DefectCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->editingName = $category->name;
        $this->dispatch('open-modal', 'edit-category');
    }

    public function update()
    {
        $this->validateOnly('editingName');

        $category = DefectCategory::findOrFail($this->editingId);
        $category->update([
            'name' => $this->editingName,
        ]);

        $this->reset(['editingId', 'editingName']);
        $this->dispatch('close-modal', 'edit-category');
        session()->flash('success', 'Category updated successfully!');
    }

    public function delete($id)
    {
        DefectCategory::findOrFail($id)->delete();
        session()->flash('success', 'Category deleted successfully!');
    }

    public function render()
    {
        $defectCategories = DefectCategory::where('name', 'like', '%' . $this->search . '%')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.qaqc.defect-category-manager', [
            'defectCategories' => $defectCategories,
        ])->extends('new.layouts.app');
    }
}
