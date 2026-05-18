<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AssetManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    public $selectedStatus = '';

    public $name, $brand, $asset_tag, $category_id, $status, $location_id, $assigned_to_user_id, $purchase_date;
    public $serial_number, $purchase_cost, $warranty_expiry, $notes;
    public $editingAssetId = null;
    public $showForm = false;

    protected $updatesQueryString = ['search', 'selectedCategory', 'selectedStatus'];

    public function render()
    {
        $assets = Asset::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('asset_tag', 'like', '%' . $this->search . '%')
                    ->orWhere('serial_number', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->paginate(10);

        return view('livewire.assets.asset-manager', [
            'assets' => $assets,
            'categories' => AssetCategory::all(),
            'locations' => AssetLocation::all(),
            'users' => User::all(),
        ]);
    }

    public function resetFields()
    {
        $this->name = '';
        $this->brand = '';
        $this->asset_tag = '';
        $this->serial_number = '';
        $this->category_id = '';
        $this->status = 'in_stock';
        $this->location_id = '';
        $this->assigned_to_user_id = '';
        $this->purchase_date = '';
        $this->purchase_cost = '';
        $this->warranty_expiry = '';
        $this->notes = '';
        $this->editingAssetId = null;
        $this->showForm = false;
    }

    public function showAddForm()
    {
        $this->resetFields();
        $this->showForm = true;
    }

    public function store()
    {
        if (!$this->editingAssetId && empty($this->asset_tag)) {
            $category = AssetCategory::find($this->category_id);
            if ($category) {
                $prefix = strtoupper(substr($category->name, 0, 3));
                $year = date('Y');
                $baseTag = "IT-{$prefix}-{$year}-";
                
                $lastAsset = Asset::where('asset_tag', 'like', $baseTag . '%')
                    ->orderBy('asset_tag', 'desc')
                    ->first();
                    
                if ($lastAsset) {
                    $lastSeq = (int) substr($lastAsset->asset_tag, -3);
                    $seq = str_pad($lastSeq + 1, 3, '0', STR_PAD_LEFT);
                } else {
                    $seq = '001';
                }
                
                $this->asset_tag = $baseTag . $seq;
            }
        }

        $this->validate([
            'name' => 'required',
            'asset_tag' => 'required|unique:assets,asset_tag,' . $this->editingAssetId,
            'category_id' => 'required',
            'status' => 'required',
        ]);

        Asset::updateOrCreate(
            ['id' => $this->editingAssetId],
            [
                'name' => $this->name,
                'brand' => $this->brand,
                'asset_tag' => $this->asset_tag,
                'serial_number' => $this->serial_number,
                'category_id' => $this->category_id,
                'status' => $this->status,
                'location_id' => $this->location_id ?: null,
                'assigned_to_user_id' => $this->assigned_to_user_id ?: null,
                'purchase_date' => $this->purchase_date ?: null,
                'purchase_cost' => $this->purchase_cost ?: null,
                'warranty_expiry' => $this->warranty_expiry ?: null,
                'notes' => $this->notes,
            ]
        );

        $this->resetFields();
        session()->flash('message', $this->editingAssetId ? 'Asset updated.' : 'Asset created.');
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        $this->editingAssetId = $id;
        $this->name = $asset->name;
        $this->brand = $asset->brand;
        $this->asset_tag = $asset->asset_tag;
        $this->serial_number = $asset->serial_number;
        $this->category_id = $asset->category_id;
        $this->status = $asset->status;
        $this->location_id = $asset->location_id;
        $this->assigned_to_user_id = $asset->assigned_to_user_id;
        $this->purchase_date = $asset->purchase_date;
        $this->purchase_cost = $asset->purchase_cost;
        $this->warranty_expiry = $asset->warranty_expiry;
        $this->notes = $asset->notes;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Asset::find($id)->delete();
        session()->flash('message', 'Asset deleted.');
    }
}
