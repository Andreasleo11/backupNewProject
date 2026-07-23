<?php

namespace App\Livewire\Navigation;

use App\Services\NavigationService;
use Livewire\Component;

class CommandPalette extends Component
{
    public bool $isOpen = false;
    public string $query = '';

    protected $listeners = [
        'open-command-palette' => 'open',
        'toggle-command-palette' => 'toggle',
    ];

    public function open(): void
    {
        $this->isOpen = true;
        $this->query = '';
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
        if ($this->isOpen) {
            $this->query = '';
        }
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->query = '';
    }

    public function render()
    {
        $menuItems = NavigationService::getSearchableMenu() ?? [];

        if (! empty($this->query)) {
            $q = strtolower(trim($this->query));
            $results = collect($menuItems)->filter(function ($item) use ($q) {
                $labelMatch = str_contains(strtolower($item['label'] ?? ''), $q);
                $parentMatch = str_contains(strtolower($item['parent_label'] ?? ''), $q);
                return $labelMatch || $parentMatch;
            })->take(10)->values()->all();
        } else {
            $results = collect($menuItems)->take(6)->values()->all();
        }

        return view('livewire.navigation.command-palette', [
            'results' => $results,
        ]);
    }
}
