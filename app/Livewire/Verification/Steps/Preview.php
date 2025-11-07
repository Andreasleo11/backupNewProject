<?php

namespace App\Livewire\Verification\Steps;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class Preview extends Component
{
    #[Modelable]
    public array $form = [];

    #[Modelable]
    public array $items = [];

    public function getTotalsProperty(): array
    {
        $subtotal = 0.0;
        foreach ($this->items as $row) {
            $qty = (float) ($row['rec_quantity'] ?? 0.0);
            $price = (float) ($row['price'] ?? 0.0);
            $subtotal += $qty * $price;
        }

        return [
            'subtotal' => $subtotal,
        ];
    }

    public function render()
    {
        return view('livewire.verification.steps.preview');
    }
}
