<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class AssetManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $selectedCategory = '';
    public $selectedStatus = '';

    public $name, $brand, $asset_tag, $category_id, $status, $location_id, $assigned_to_user_id, $assigned_to_nik, $purchase_date;
    public $serial_number, $purchase_cost, $warranty_expiry, $notes;
    public $ip_address, $username, $purpose, $os, $position_image, $department_id;
    public $editingAssetId = null;
    public $showForm = false;

    // Advanced Filters
    public $filterIpAddress = '';
    public $filterUsername = '';
    public $filterPurpose = '';
    public $filterOs = '';
    public $filterDepartmentId = '';
    public $filterBrand = '';

    protected $updatesQueryString = [
        'search', 'selectedCategory', 'selectedStatus',
        'filterIpAddress', 'filterUsername', 'filterPurpose',
        'filterOs', 'filterDepartmentId', 'filterBrand'
    ];

    public function render()
    {
        $assets = Asset::query()
            ->with(['category', 'location', 'employee', 'assignedTo', 'department'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('asset_tag', 'like', '%' . $this->search . '%')
                    ->orWhere('serial_number', 'like', '%' . $this->search . '%')
                    ->orWhere('ip_address', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->when($this->selectedStatus, function ($query) {
                $query->where('status', $this->selectedStatus);
            })
            ->when($this->filterIpAddress, function ($query) {
                $query->where('ip_address', 'like', '%' . $this->filterIpAddress . '%');
            })
            ->when($this->filterUsername, function ($query) {
                $query->where('username', 'like', '%' . $this->filterUsername . '%');
            })
            ->when($this->filterPurpose, function ($query) {
                $query->where('purpose', 'like', '%' . $this->filterPurpose . '%');
            })
            ->when($this->filterOs, function ($query) {
                $query->where('os', 'like', '%' . $this->filterOs . '%');
            })
            ->when($this->filterDepartmentId, function ($query) {
                $query->where('department_id', $this->filterDepartmentId);
            })
            ->when($this->filterBrand, function ($query) {
                $query->where('brand', 'like', '%' . $this->filterBrand . '%');
            })
            ->paginate(10);

        return view('livewire.assets.asset-manager', [
            'assets' => $assets,
            'categories' => AssetCategory::all(),
            'locations' => AssetLocation::all(),
            'employees' => \App\Infrastructure\Persistence\Eloquent\Models\Employee::all(),
            'departments' => \App\Infrastructure\Persistence\Eloquent\Models\Department::all(),
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
        $this->assigned_to_nik = '';
        $this->purchase_date = '';
        $this->purchase_cost = '';
        $this->warranty_expiry = '';
        $this->notes = '';
        $this->ip_address = '';
        $this->username = '';
        $this->purpose = '';
        $this->os = '';
        $this->position_image = null;
        $this->department_id = '';
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
            'ip_address' => 'nullable|string|max:255',
            'username' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'os' => 'nullable|string|max:255',
            'position_image' => 'nullable|' . (is_object($this->position_image) ? 'image|max:2048' : 'string'),
            'department_id' => 'nullable|exists:departments,id',
        ]);

        $imagePath = $this->position_image;
        if (is_object($this->position_image)) {
            $imagePath = $this->position_image->store('assets', 'public');
        }

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
                'assigned_to_nik' => $this->assigned_to_nik ?: null,
                'purchase_date' => $this->purchase_date ?: null,
                'purchase_cost' => $this->purchase_cost ?: null,
                'warranty_expiry' => $this->warranty_expiry ?: null,
                'notes' => $this->notes,
                'ip_address' => $this->ip_address ?: null,
                'username' => $this->username ?: null,
                'purpose' => $this->purpose ?: null,
                'os' => $this->os ?: null,
                'position_image' => $imagePath ?: null,
                'department_id' => $this->department_id ?: null,
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
        $this->assigned_to_nik = $asset->assigned_to_nik;
        $this->purchase_date = $asset->purchase_date;
        $this->purchase_cost = $asset->purchase_cost;
        $this->warranty_expiry = $asset->warranty_expiry;
        $this->notes = $asset->notes;
        $this->ip_address = $asset->ip_address;
        $this->username = $asset->username;
        $this->purpose = $asset->purpose;
        $this->os = $asset->os;
        $this->position_image = $asset->position_image;
        $this->department_id = $asset->department_id;
        $this->showForm = true;
    }

    public function delete($id)
    {
        Asset::find($id)->delete();
        session()->flash('message', 'Asset deleted.');
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->selectedCategory = '';
        $this->selectedStatus = '';
        $this->filterIpAddress = '';
        $this->filterUsername = '';
        $this->filterPurpose = '';
        $this->filterOs = '';
        $this->filterDepartmentId = '';
        $this->filterBrand = '';
    }

    public function export()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AssetExport(), 'assets.xlsx');
    }
}
