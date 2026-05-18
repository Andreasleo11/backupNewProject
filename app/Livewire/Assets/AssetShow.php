<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use Livewire\Component;

class AssetShow extends Component
{
    public $asset;

    public function mount($id)
    {
        $this->asset = Asset::with(['category', 'location', 'assignedTo'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.assets.asset-show');
    }
}
