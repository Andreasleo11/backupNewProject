<?php

namespace App\Livewire\Layout;

use Livewire\Attributes\On;
use Livewire\Component;

class FlashMessages extends Component
{
    public ?string $type = null;
    public ?string $message = null;

    #[On('flash')]
    public function showFlash(string $type, string $message): void
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function clear(): void
    {
        $this->reset(['type', 'message']);
    }

    public function render()
    {
        return view('livewire.layout.flash-messages');
    }
}

