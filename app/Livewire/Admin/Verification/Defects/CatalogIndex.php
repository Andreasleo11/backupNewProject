<?php

namespace App\Livewire\Admin\Verification\Defects;

use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public ?bool $active = null;

    public function toggleActive(int $id): void
    {
        if ($m = DefectCatalog::find($id)) {
            $m->update(['active' => ! $m->active]);
        }
    }

    public function delete(int $id): void
    {
        DefectCatalog::whereKey($id)->delete();
        session()->flash('ok', 'Deleted.');
    }

    public function render()
    {
        $q = DefectCatalog::query()
            ->when($this->search, function ($qq) {
                $s = "%{$this->search}%";
                $qq->where('code', 'like', $s)->orWhere('name', 'like', $s);
            })
            ->when(! is_null($this->active), fn ($qq) => $qq->where('active', $this->active))
            ->orderBy('code');

        return view('livewire.admin.verification.defects.catalog-index', [
            'rows' => $q->paginate(15),
        ]);
    }
}
