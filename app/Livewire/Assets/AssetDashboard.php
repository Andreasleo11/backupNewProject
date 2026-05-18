<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Consumable;
use Livewire\Component;

class AssetDashboard extends Component
{
    public $totalAssets = 0;
    public $lowStockConsumables = 0;
    public $assignedAssets = 0;
    public $availableAssets = 0;

    public function mount()
    {
        $this->totalAssets = Asset::count();
        $this->lowStockConsumables = Consumable::whereColumn('current_stock', '<=', 'min_stock')->count();
        $this->assignedAssets = Asset::where('status', 'assigned')->count();
        $this->availableAssets = Asset::where('status', 'in_stock')->count();
    }

    public function render()
    {
        return view('livewire.assets.asset-dashboard');
    }
}
