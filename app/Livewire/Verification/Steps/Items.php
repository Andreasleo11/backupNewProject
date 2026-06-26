<?php

namespace App\Livewire\Verification\Steps;

use App\Livewire\Verification\Concerns\VerificationRules;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\On;
use Livewire\Component;

class Items extends Component
{
    use VerificationRules;

    #[Modelable]
    public array $items = [];

    public ?string $customer = null;

    public string $defaultCurrency = 'IDR';

    public ?int $activeItem = null;

    public bool $pasteDialog = false;

    public string $pasteBuffer = '';

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
        $this->validate($this->rulesItems());
    }

    #[On('request-validate')]
    public function handleValidation(int $step): void
    {
        if ($step !== 2) {
            return;
        }

        try {
            $this->validate($this->rulesItems());
            $this->resetErrorBag();
            $this->dispatch('step-valid', step: 2);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->setErrorBag($e->validator->errors());
            $this->dispatch('step-invalid', step: 2, errors: $e->errors());

            return;
        }
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
            $this->activeItem = 0;
            $this->dispatch('active-item-updated', index: 0);
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if ($this->activeItem !== null) {
            if (! array_key_exists($this->activeItem, $this->items)) {
                $this->activeItem = count($this->items) ? 0 : null;
                $this->dispatch('active-item-updated', index: $this->activeItem);
            }
        }
    }

    public function applyDefaultCurrency(): void
    {
        foreach ($this->items as &$r) {
            $r['currency'] = $this->defaultCurrency ?: ($r['currency'] ?? 'IDR');
        }
        unset($r);
    }

    public function fillCantUseFromDefects(int $i): void
    {
        $sum = collect($this->items[$i]['defects'] ?? [])->sum(fn ($d) => (float) ($d['quantity'] ?? 0));
        $this->items[$i]['cant_use'] = round((float) $sum, 4);
    }

    public function fillAllCantUseFromDefects(): void
    {
        foreach ($this->items as $i => $_) {
            $this->fillCantUseFromDefects($i);
        }
    }

    public function insertItemBelow(int $i): void
    {
        $empty = [
            'part_name' => '',
            'rec_quantity' => 0,
            'verify_quantity' => 0,
            'can_use' => 0,
            'cant_use' => 0,
            'price' => 0,
            'currency' => $this->defaultCurrency ?: 'IDR',
            'defects' => [],
        ];
        array_splice($this->items, $i + 1, 0, [$empty]);
    }

    public function duplicateItem(int $i): void
    {
        $copy = $this->items[$i];
        array_splice($this->items, $i + 1, 0, [$copy]);
    }

    public function moveItemUp(int $i): void
    {
        if ($i > 0) {
            [$this->items[$i - 1], $this->items[$i]] = [$this->items[$i], $this->items[$i - 1]];
        }
    }

    public function moveItemDown(int $i): void
    {
        if ($i < count($this->items) - 1) {
            [$this->items[$i + 1], $this->items[$i]] = [$this->items[$i], $this->items[$i + 1]];
        }
    }

    /* ---------------- Paste handler ---------------- */

    public function applyPastedItems(): void
    {
        $rows = preg_split('/\r\n|\r|\n/', trim($this->pasteBuffer));
        $addedCount = 0;
        $duplicateCount = 0;

        foreach ($rows as $line) {
            if (trim($line) === '') {
                continue;
            }
            $cols = str_getcsv($line, (str_contains($line, "\t") ? "\t" : ','));
            
            $partName = mb_substr(trim($cols[0] ?? ''), 0, 255);
            if ($partName === '') {
                continue;
            }

            $recQty = max(0.0, (float) ($cols[1] ?? 0));
            $verifyQty = max(0.0, (float) ($cols[2] ?? 0));
            $canUse = max(0.0, (float) ($cols[3] ?? 0));
            $cantUse = max(0.0, (float) ($cols[4] ?? 0));
            $price = max(0.0, (float) ($cols[5] ?? 0));
            $currency = mb_substr(trim($cols[6] ?? ($this->defaultCurrency ?: 'IDR')), 0, 10);

            // Check if this exact item already exists in the list to prevent double pasting
            $isDuplicate = false;
            foreach ($this->items as $existing) {
                if (strcasecmp($existing['part_name'], $partName) === 0 &&
                    (float)$existing['rec_quantity'] === $recQty &&
                    (float)$existing['verify_quantity'] === $verifyQty &&
                    (float)$existing['price'] === $price) {
                    $isDuplicate = true;
                    break;
                }
            }

            if ($isDuplicate) {
                $duplicateCount++;
                continue;
            }

            $this->items[] = [
                'part_name' => $partName,
                'rec_quantity' => $recQty,
                'verify_quantity' => $verifyQty,
                'can_use' => $canUse,
                'cant_use' => $cantUse,
                'price' => $price,
                'currency' => $currency,
                'defects' => [],
            ];
            $addedCount++;
        }

        $this->pasteBuffer = '';
        $this->pasteDialog = false;

        if ($this->activeItem === null && count($this->items)) {
            $this->activeItem = 0;
            $this->dispatch('active-item-updated', index: 0);
        }

        if ($duplicateCount > 0) {
            session()->flash('warning', "Imported {$addedCount} items. Ignored {$duplicateCount} duplicate rows.");
        } else {
            session()->flash('ok', "Imported {$addedCount} items successfully.");
        }
    }

    public function render()
    {
        return view('livewire.verification.steps.items');
    }
}
