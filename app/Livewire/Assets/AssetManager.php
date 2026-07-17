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

    public \App\Livewire\Forms\AssetForm $form;
    
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
            ->when($this->filterDepartmentId, function ($query) {
                $query->where('department_id', $this->filterDepartmentId);
            });
            
        $filters = [
            'ip_address' => $this->filterIpAddress,
            'username' => $this->filterUsername,
            'purpose' => $this->filterPurpose,
            'os' => $this->filterOs,
            'brand' => $this->filterBrand,
        ];

        foreach ($filters as $field => $value) {
            $assets->when($value, function ($query) use ($field, $value) {
                $query->where($field, 'like', '%' . $value . '%');
            });
        }

        $assets = $assets->paginate(10);

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
        $this->form->reset();
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
        if (!$this->editingAssetId && empty($this->form->asset_tag)) {
            $category = AssetCategory::find($this->form->category_id);
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
                
                $this->form->asset_tag = $baseTag . $seq;
            }
        }

        $this->validate([
            'form.asset_tag' => 'required|unique:assets,asset_tag,' . $this->editingAssetId,
            'form.position_image' => 'nullable|' . (is_object($this->form->position_image) ? 'image|max:2048' : 'string'),
        ]);

        $imagePath = is_object($this->form->position_image) 
            ? $this->form->position_image->store('assets', 'public') 
            : $this->form->position_image;

        $this->form->save($imagePath);

        $this->resetFields();
        session()->flash('message', $this->editingAssetId ? 'Asset updated.' : 'Asset created.');
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        $this->editingAssetId = $id;
        $this->form->setAsset($asset);
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
