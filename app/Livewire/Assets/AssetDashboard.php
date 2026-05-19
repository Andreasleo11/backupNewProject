<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\Consumable;
use Livewire\Component;

class AssetDashboard extends Component
{
    public $totalAssets = 0;
    public $totalConsumablesInStock = 0;
    public $lowStockConsumables = 0;
    public $assignedAssets = 0;
    public $warrantyExpiring30 = 0;
    public $warrantyExpiring60 = 0;
    public $recentAssets = [];
    public $recentConsumableIssues = [];

    public function mount()
    {
        $this->totalAssets = Asset::count();
        $this->totalConsumablesInStock = Consumable::sum('current_stock');
        $this->lowStockConsumables = Consumable::whereColumn('current_stock', '<=', 'min_stock')->count();
        $this->assignedAssets = Asset::where('status', 'assigned')->count();
        $this->warrantyExpiring30 = Asset::whereNotNull('warranty_expiry')->whereBetween('warranty_expiry', [now(), now()->addDays(30)])->count();
        $this->warrantyExpiring60 = Asset::whereNotNull('warranty_expiry')->whereBetween('warranty_expiry', [now(), now()->addDays(60)])->count();
        $this->recentAssets = Asset::with(['category','location','assignedTo'])->orderByDesc('created_at')->limit(5)->get();
        $this->recentConsumableIssues = \App\Models\StockTransaction::with(['consumable','user','targetUser'])->where('type','Out')->orderByDesc('created_at')->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.assets.asset-dashboard');
    }
}
