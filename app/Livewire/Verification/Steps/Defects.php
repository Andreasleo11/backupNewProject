<?php

namespace App\Livewire\Verification\Steps;

use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class Defects extends Component
{
    #[Modelable]
    public array $items = [];

    public ?int $activeItem = null;

    public ?int $pickerForItem = null;

    public string $defectSearch = '';

    public array $catalogResults = [];

    public function addDefect(int $itemIndex): void
    {
        $this->items[$itemIndex]['defects'][] = [
            'code' => null,
            'name' => '',
            'severity' => 'LOW',
            'source' => 'DAIJO',
            'quantity' => null,
            'notes' => null,
        ];
    }

    public function removeDefect(int $itemIndex, int $defectIndex): void
    {
        unset($this->items[$itemIndex]['defects'][$defectIndex]);
        $this->items[$itemIndex]['defects'] = array_values($this->items[$itemIndex]['defects'] ?? []);

    }

    /* ---------------- Catalog picker ---------------- */

    public function openDefectPicker(int $itemIndex): void
    {
        $this->pickerForItem = $itemIndex;
        $this->defectSearch = '';
        $this->catalogResults = $this->searchCatalog('');
    }

    public function closeDefectPicker(): void
    {
        $this->pickerForItem = null;
        $this->defectSearch = '';
        $this->catalogResults = [];
    }

    public function updatedDefectSearch(): void
    {
        $this->catalogResults = $this->searchCatalog($this->defectSearch);
    }

    private function searchCatalog(string $term): array
    {
        $q = DefectCatalog::query()->where('active', true);
        if (trim($term) !== '') {
            $s = "%{$term}%";
            $q->where(fn ($qq) => $qq->where('code', 'like', $s)->orWhere('name', 'like', $s));
        }

        return $q->orderBy('code')->limit(15)->get()->map(fn ($r) => [
            'id' => $r->id,
            'code' => $r->code,
            'name' => $r->name,
            'severity' => $r->default_severity?->value ?? (string) $r->default_severity,
            'source' => $r->default_source?->value ?? (string) $r->default_source,
            'quantity' => (float) $r->default_quantity,
            'notes' => $r->notes,
        ])->toArray();
    }

    public function pickCatalogDefect(int $catalogId): void
    {
        if ($this->pickerForItem === null) {
            return;
        }

        $c = DefectCatalog::findOrFail($catalogId);
        $row = [
            'code' => $c->code,
            'name' => $c->name,
            'severity' => $c->default_severity?->value ?? (string) $c->default_severity,
            'source' => $c->default_source?->value ?? (string) $c->default_source,
            'quantity' => (float) $c->default_quantity,
            'notes' => $c->notes,
        ];
        $this->items[$this->pickerForItem]['defects'] =
            array_values(array_merge($this->items[$this->pickerForItem]['defects'] ?? [], [$row]));

        $this->closeDefectPicker();
    }

    public function goToItem(int $i): void
    {
        if (! array_key_exists($i, $this->items)) {
            return;
        }
        $this->activeItem = $i;
    }

    public function render()
    {
        return view('livewire.verification.steps.defects');
    }
}
