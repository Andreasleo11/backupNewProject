<?php

namespace App\Livewire\Requirements;

use App\Models\Requirement;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $filterFreq = null;        // '', once, yearly, quarterly, monthly

    public ?string $filterApproval = null;    // '', '1', '0'

    public string $sort = 'name';

    public string $dir = 'asc';

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'filterFreq' => ['except' => null],
        'filterApproval' => ['except' => null],
        'sort' => ['except' => 'name'],
        'dir' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function updated($field)
    {
        if (in_array($field, ['search', 'filterFreq', 'filterApproval', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sort === $field) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->dir = 'asc';
        }
        $this->resetPage();
    }

    public function toggleDir(): void
    {
        $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function render()
    {
        $items = Requirement::query()
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term);
                });
            })
            ->when($this->filterFreq, fn ($q, $f) => $q->where('frequency', $f))
            ->when($this->filterApproval !== null && $this->filterApproval !== '', fn ($q) => $q->where('requires_approval', (int) $this->filterApproval)
            )
            ->orderBy($this->sort, $this->dir)
            ->paginate($this->perPage);

        return view('livewire.requirements.index', compact('items'));
    }
}
