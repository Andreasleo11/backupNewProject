<?php

namespace App\Livewire\Components;

use Livewire\Component;

class SearchableDropdown extends Component
{
    public $search = '';
    public $results = [];
    public $page = 1;
    public $perPage = 10;
    public $hasMore = true;
    public $options = []; // e.g. ['distinct' => true, 'limit' => 50]

    public $model;
    public $column;
    public $label = 'Select';
    public $labelHtml;
    public $value;
    public $placeholder = 'Search...';
    public $name;
    public $hasError = false;

    public function mount()
    {
        $this->options = array_merge([
            'distinct' => false,
            // other defaults here
        ], $this->options);

        if ($this->value && !$this->search) {
            $this->search = $this->value; // show selected item on load
        }
    }

    public function updatedSearch($value)
    {
        $this->page = 1;
        $this->hasMore = true;
        $this->results = [];

        $this->loadMore();
    }

    public function loadMore()
    {
        if (!class_exists($this->model)) return;

        $query = $this->model::query()
            ->when($this->options['distinct'] ?? false, fn($q) => $q->distinct())
            ->where($this->column, 'like', "%{$this->search}%");

        $items = $query->skip(($this->page - 1) * $this->perPage)
            ->take($this->perPage)
            ->pluck($this->column)
            ->toArray();

        $this->results = array_merge($this->results, $items);
        $this->hasMore = count($items) === $this->perPage;
        $this->page++;
    }

    public function select($value)
    {
        $record = $this->model::where($this->column, $value)->first();

        if (!$record) return;

        $this->search = $record->{$this->column};
        $this->value = $record->{$this->column};
        $this->results = [];

        // For fields like part_number or part_name, emit full record
        if (in_array($this->name, ['part_number', 'part_name'])) {
            $this->dispatch('dropdownSelected', [
                'field' => $this->name,
                'item_no' => $record->item_no,
                'description' => $record->description,
            ]);
        } else {
            // Emit plain value for other fields (e.g. customer)
            $this->dispatch('dropdownSelected', ['field' => $this->name, 'value' => $record->{$this->column}]);
        }
    }

    public function render()
    {
        return view('livewire.components.searchable-dropdown');
    }
}
