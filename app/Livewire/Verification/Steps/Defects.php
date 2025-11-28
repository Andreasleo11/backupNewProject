<?php

namespace App\Livewire\Verification\Steps;

use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use App\Livewire\Verification\Concerns\VerificationRules;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;

class Defects extends Component
{
    use VerificationRules;

    #[Modelable]
    public array $items = [];

    public ?int $activeItem = null;

    public ?int $pickerForItem = null;

    public string $defectSearch = '';

    public array $catalogResults = [];

    protected function messages(): array
    {
        return method_exists($this, 'messagesAll') ? $this->messagesAll() : [];
    }

    protected function validationAttributes(): array
    {
        return method_exists($this, 'attributesAll') ? $this->attributesAll() : [];
    }

    public function updated()
    {
        $this->validate($this->rulesDefects());
    }

    #[On('request-validate')]
    public function handleValidation(int $step): void
    {
        if ($step !== 3) {
            return;
        }

        try {
            $this->validate($this->rulesDefects());
            $this->resetErrorBag();
            $this->dispatch('step-valid', step: 3);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());

            if ($path = $this->firstErrorPath($e->errors())) {
                $this->activeItem = $path['item'];
                $this->dispatch('focus-field', key: $path['key']);
            }
            $this->dispatch('step-invalid', step: 3, errors: $e->errors());

            return;
        }

    }

    /** Parse "items.2.defects.1.name" -> ['item' => 2, 'defect' => 1, 'field' => 'name', 'key' => originalKey] */
    private function firstErrorPath(array $errors): ?array
    {
        foreach ($errors as $key => $msgs) {
            if (str_starts_with($key, 'items.')) {
                $parts = explode('.', $key);
                // items . {i} . defects . {d} . {field}
                $item = isset($parts[1]) ? (int) $parts[1] : null;
                $defect = isset($parts[3]) ? (int) $parts[3] : null;
                $field = $parts[4] ?? null;

                return ['item' => $item, 'defect' => $defect, 'field' => $field, 'key' => $key];
            }
        }

        return null;
    }

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
        $idx = array_key_last($this->items);
        $this->dispatch('focus-field', key: "items.$idx.name");
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
        dd($this->catalogResults);
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
