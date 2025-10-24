<?php

namespace App\Livewire\Verification\Steps;

use Livewire\Attributes\Modelable;
use Livewire\Component;

class Header extends Component
{
    #[Modelable]
    public array $form = [];

    public function render()
    {
        return view('livewire.verification.steps.header');
    }
}
