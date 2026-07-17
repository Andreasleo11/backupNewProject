<?php

namespace App\Livewire\Verification\Steps;

use App\Livewire\Verification\Concerns\VerificationRules;
use App\Models\MasterDataRogPartName;
use App\Models\MasterDataPartPriceLog;
use App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Items extends Component
{
    use VerificationRules;

    #[Modelable]
    public array $items = [];

    public ?string $customer = null;

    public string $defaultCurrency = 'IDR';

    #[Reactive]
    public ?int $activeItem = null;

    public array $partSuggestions = [];

    // Catalog lookup & suggestions for defects
    public ?int $pickerForItem = null;
    public string $defectSearch = '';
    public array $catalogResults = [];
    public array $defectSuggestions = [];
    public string $activeTab = 'details';

    #[On('switch-tab')]
    public function handleSwitchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    protected function messages(): array
    {
        return method_exists($this, 'messagesAll') ? $this->messagesAll() : [];
    }

    protected function validationAttributes(): array
    {
        return method_exists($this, 'attributesAll') ? $this->attributesAll() : [];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, $this->rulesItems());
        $this->validateOnly($propertyName, $this->rulesDefects());
    }

    public function updatedItems($value, $key): void
    {
        // 1. Part Name Suggestions & Price fetch
        if (str_ends_with($key, '.part_name')) {
            $parts = explode('.', $key);
            $index = (int)$parts[0];

            if ($value) {
                $this->partSuggestions[$index] = MasterDataRogPartName::where('name', 'like', "%{$value}%")
                    ->orderBy('name')
                    ->limit(15)
                    ->pluck('name')
                    ->toArray();

                $partCode = explode('/', $value)[0];
                if ($partCode) {
                    $latestPrice = MasterDataPartPriceLog::where('part_code', $partCode)
                        ->orderByDesc('created_at')
                        ->orderByDesc('id')
                        ->value('price');
                    if ($latestPrice !== null) {
                        $this->items[$index]['price'] = (float)$latestPrice;
                    }
                }
            } else {
                $this->partSuggestions[$index] = [];
            }
        }

        // 2. Defect Name Suggestions & Catalog Auto-fill
        if (str_contains($key, '.defects.') && (str_ends_with($key, '.name') || str_ends_with($key, '.code'))) {
            $parts = explode('.', $key);
            $itemIndex = (int)$parts[1];
            $defectIndex = (int)$parts[3];
            $searchField = str_ends_with($key, '.name') ? 'name' : 'code';

            if ($value) {
                $suggestions = DefectCatalog::where('active', true)
                    ->where($searchField, 'like', "%{$value}%")
                    ->limit(15)
                    ->get();

                $this->defectSuggestions[$defectIndex] = $suggestions->map(fn($r) => [
                    'code' => $r->code,
                    'name' => $r->name,
                    'severity' => $r->default_severity?->value ?? (string)$r->default_severity,
                    'source' => $r->default_source?->value ?? (string)$r->default_source,
                    'quantity' => (int)$r->default_quantity,
                    'notes' => $r->notes,
                ])->toArray();

                $match = $suggestions->first(fn($r) => strcasecmp(trim($r->{$searchField}), trim($value)) === 0);
                if ($match) {
                    $this->items[$itemIndex]['defects'][$defectIndex]['code'] = $match->code;
                    $this->items[$itemIndex]['defects'][$defectIndex]['name'] = $match->name;
                    $this->items[$itemIndex]['defects'][$defectIndex]['severity'] = $match->default_severity?->value ?? (string)$match->default_severity;
                    $this->items[$itemIndex]['defects'][$defectIndex]['source'] = $match->default_source?->value ?? (string)$match->default_source;
                    $this->items[$itemIndex]['defects'][$defectIndex]['quantity'] = (int)$match->default_quantity;
                    if (empty($this->items[$itemIndex]['defects'][$defectIndex]['notes'])) {
                        $this->items[$itemIndex]['defects'][$defectIndex]['notes'] = $match->notes;
                    }
                    
                    // Automatically re-calculate Cant Use quantity after auto-filling defect quantity
                    $this->fillCantUseFromDefects($itemIndex);
                } else {
                    // If no longer a catalog match, check if it had a catalog code
                    // and replace it with an auto-generated CUST- code.
                    $currentCode = $this->items[$itemIndex]['defects'][$defectIndex]['code'] ?? '';
                    if (empty($currentCode) || !str_starts_with($currentCode, 'CUST-')) {
                        $this->items[$itemIndex]['defects'][$defectIndex]['code'] = 'CUST-' . strtoupper(\Illuminate\Support\Str::random(6));
                    }
                }
            } else {
                $this->defectSuggestions[$defectIndex] = [];
            }
        }
    }

    #[On('request-validate')]
    public function handleValidation(int $step): void
    {
        if ($step !== 2) {
            return;
        }

        try {
            $this->validate($this->rulesItems());
            $this->validate($this->rulesDefects());
            $this->resetErrorBag();
            $this->dispatch('step-valid', step: 2);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->dispatch('step-invalid', step: 2, errors: $e->errors());
            return;
        }
    }

    public function selectItem(int $i): void
    {
        $this->dispatch('go-to-item', index: $i);
    }

    public function addItem(): void
    {
        $this->items[] = [
            'part_name' => '',
            'rec_quantity' => null,
            'verify_quantity' => null,
            'can_use' => null,
            'cant_use' => null,
            'price' => 0,
            'currency' => $this->defaultCurrency ?: 'IDR',
            'defects' => [],
        ];
        if ($this->activeItem === null) {
            $this->dispatch('go-to-item', index: 0);
        } else {
            $this->dispatch('go-to-item', index: count($this->items) - 1);
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if ($this->activeItem !== null) {
            if (! array_key_exists($this->activeItem, $this->items)) {
                $newActive = count($this->items) ? 0 : null;
                if ($newActive !== null) {
                    $this->dispatch('go-to-item', index: $newActive);
                } else {
                    $this->dispatch('active-item-updated', index: null);
                }
            }
        }
    }

    public function fillCantUseFromDefects(int $i): void
    {
        $sum = collect($this->items[$i]['defects'] ?? [])->sum(fn ($d) => (int) ($d['quantity'] ?? 0));
        $this->items[$i]['cant_use'] = (int) $sum;
    }

    public function insertItemBelow(int $i): void
    {
        $empty = [
            'part_name' => '',
            'rec_quantity' => null,
            'verify_quantity' => null,
            'can_use' => null,
            'cant_use' => null,
            'price' => 0,
            'currency' => $this->defaultCurrency ?: 'IDR',
            'defects' => [],
        ];
        array_splice($this->items, $i + 1, 0, [$empty]);
        $this->dispatch('go-to-item', index: $i + 1);
    }

    public function duplicateItem(int $i): void
    {
        $copy = $this->items[$i];
        array_splice($this->items, $i + 1, 0, [$copy]);
        $this->dispatch('go-to-item', index: $i + 1);
    }

    public function moveItemUp(int $i): void
    {
        if ($i > 0) {
            [$this->items[$i - 1], $this->items[$i]] = [$this->items[$i], $this->items[$i - 1]];
            
            $newActive = $this->activeItem;
            if ($this->activeItem === $i) {
                $newActive = $i - 1;
            } elseif ($this->activeItem === $i - 1) {
                $newActive = $i;
            }
            $this->dispatch('go-to-item', index: $newActive);
        }
    }

    public function moveItemDown(int $i): void
    {
        if ($i < count($this->items) - 1) {
            [$this->items[$i + 1], $this->items[$i]] = [$this->items[$i], $this->items[$i + 1]];
            
            $newActive = $this->activeItem;
            if ($this->activeItem === $i) {
                $newActive = $i + 1;
            } elseif ($this->activeItem === $i + 1) {
                $newActive = $i;
            }
            $this->dispatch('go-to-item', index: $newActive);
        }
    }

    public function openDefectPicker(int $itemIndex): void
    {
        $this->pickerForItem = $itemIndex;
        $this->defectSearch = '';
        $this->catalogResults = DefectCatalog::where('active', true)
            ->orderBy('code')
            ->limit(15)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'code' => $r->code,
                'name' => $r->name,
                'severity' => $r->default_severity?->value ?? (string)$r->default_severity,
                'source' => $r->default_source?->value ?? (string)$r->default_source,
                'quantity' => (int)$r->default_quantity,
                'notes' => $r->notes,
            ])
            ->toArray();
    }

    public function closeDefectPicker(): void
    {
        $this->pickerForItem = null;
        $this->defectSearch = '';
        $this->catalogResults = [];
    }

    public function updatedDefectSearch(): void
    {
        $s = "%{$this->defectSearch}%";
        $q = DefectCatalog::where('active', true);
        if ($this->defectSearch !== '') {
            $q->where(fn ($qq) => $qq->where('code', 'like', $s)->orWhere('name', 'like', $s));
        }
        $this->catalogResults = $q->orderBy('code')
            ->limit(15)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'code' => $r->code,
                'name' => $r->name,
                'severity' => $r->default_severity?->value ?? (string)$r->default_severity,
                'source' => $r->default_source?->value ?? (string)$r->default_source,
                'quantity' => (int)$r->default_quantity,
                'notes' => $r->notes,
            ])
            ->toArray();
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
            'quantity' => (int) $c->default_quantity,
            'notes' => $c->notes,
        ];
        $this->items[$this->pickerForItem]['defects'] =
            array_values(array_merge($this->items[$this->pickerForItem]['defects'] ?? [], [$row]));

        // Update cant_use automatically
        $this->fillCantUseFromDefects($this->pickerForItem);

        $this->closeDefectPicker();
    }

    public function addDefect(int $itemIndex): void
    {
        $this->addCustomDefect($itemIndex);
    }

    public function addCustomDefect(int $itemIndex): void
    {
        $this->items[$itemIndex]['defects'][] = [
            'code' => 'CUST-' . strtoupper(\Illuminate\Support\Str::random(6)),
            'name' => '',
            'severity' => 'LOW',
            'source' => 'DAIJO',
            'quantity' => null,
            'notes' => null,
        ];
        $this->closeDefectPicker();
        $defectIdx = array_key_last($this->items[$itemIndex]['defects']);
        $this->dispatch('focus-field', key: "items.{$itemIndex}.defects.{$defectIdx}.name");
    }

    public function removeDefect(int $itemIndex, int $defectIndex): void
    {
        unset($this->items[$itemIndex]['defects'][$defectIndex]);
        $this->items[$itemIndex]['defects'] = array_values($this->items[$itemIndex]['defects']);
        $this->fillCantUseFromDefects($itemIndex);
    }

    public function render()
    {
        return view('livewire.verification.steps.items');
    }
}
