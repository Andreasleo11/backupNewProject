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

    public function mount(): void
    {
        // Read Laravel session flashes
        if (session()->has('success')) {
            $this->type = 'success';
            $this->message = session('success');
        } elseif (session()->has('error')) {
            $this->type = 'error';
            $this->message = session('error');
        } elseif (session()->has('warning')) {
            $this->type = 'warning';
            $this->message = session('warning');
        } elseif (session()->has('info')) {
            $this->type = 'info';
            $this->message = session('info');
        }
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
