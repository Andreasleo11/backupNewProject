<?php

namespace App\Livewire\Verification\Steps;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class Items extends Component
{
    #[Modelable]
    public array $items = [];

    public ?string $customer = null;

    public string $defaultCurrency = 'IDR';

    public ?int $activeItem = null;

    public bool $pasteDialog = false;

    public string $pasteBuffer = '';

    public function addItem(): void
    {
        $this->items[] = [
            'part_name' => '',
            'rec_quantity' => 0,
            'verify_quantity' => 0,
            'can_use' => 0,
            'cant_use' => 0,
            'price' => 0,
            'currency' => $this->defaultCurrency ?: 'IDR',
            'defects' => [],
        ];
        if ($this->activeItem === null) {
            $this->activeItem = 0;
        }
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if ($this->activeItem !== null) {
            if (! array_key_exists($this->activeItem, $this->items)) {
                $this->activeItem = count($this->items) ? 0 : null;
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
        foreach ($rows as $line) {
            if ($line === '') {
                continue;
            }
            $cols = str_getcsv($line, (str_contains($line, "\t") ? "\t" : ','));
            $this->items[] = [
                'part_name' => (string) ($cols[0] ?? ''),
                'rec_quantity' => (float) ($cols[1] ?? 0),
                'verify_quantity' => (float) ($cols[2] ?? 0),
                'can_use' => (float) ($cols[3] ?? 0),
                'cant_use' => (float) ($cols[4] ?? 0),
                'price' => (float) ($cols[5] ?? 0),
                'currency' => (string) ($cols[6] ?? ($this->defaultCurrency ?: 'IDR')),
                'defects' => [],
            ];
        }
        $this->pasteBuffer = '';
        $this->pasteDialog = false;
        if ($this->activeItem === null && count($this->items)) {
            $this->activeItem = 0;
        }
    }

    public function render()
    {
        return view('livewire.verification.steps.items');
    }
}
