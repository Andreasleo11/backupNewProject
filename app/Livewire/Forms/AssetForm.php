<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use Livewire\Attributes\Validate;
use App\Models\Asset;

class AssetForm extends Form
{
    public ?Asset $asset = null;

    #[Validate('required')]
    public $name = '';
    
    public $brand = '';
    
    #[Validate('required')]
    public $asset_tag = '';
    
    public $serial_number = '';
    
    #[Validate('required')]
    public $category_id = '';
    
    #[Validate('required')]
    public $status = 'in_stock';
    
    public $location_id = '';
    public $assigned_to_user_id = '';
    public $assigned_to_nik = '';
    public $purchase_date = '';
    public $purchase_cost = '';
    public $warranty_expiry = '';
    public $notes = '';
    
    #[Validate('nullable|string|max:255')]
    public $ip_address = '';
    
    #[Validate('nullable|string|max:255')]
    public $username = '';
    
    #[Validate('nullable|string|max:255')]
    public $purpose = '';
    
    #[Validate('nullable|string|max:255')]
    public $os = '';
    
    #[Validate('nullable|exists:departments,id')]
    public $department_id = '';
    
    public $position_image = null;

    public function setAsset(Asset $asset)
    {
        $this->asset = $asset;
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
        $this->department_id = $asset->department_id;
        $this->position_image = $asset->position_image;
    }

    public function save($imagePath = null)
    {
        $this->validate();

        $data = $this->only([
            'name', 'brand', 'asset_tag', 'serial_number', 'category_id', 'status',
            'location_id', 'assigned_to_user_id', 'assigned_to_nik', 'purchase_date',
            'purchase_cost', 'warranty_expiry', 'notes', 'ip_address', 'username',
            'purpose', 'os', 'department_id'
        ]);

        // Fix empty strings to null for nullable foreign keys and dates
        $nullableFields = ['location_id', 'assigned_to_user_id', 'assigned_to_nik', 'purchase_date', 'purchase_cost', 'warranty_expiry', 'ip_address', 'username', 'purpose', 'os', 'department_id'];
        foreach ($nullableFields as $field) {
            if (empty($data[$field])) {
                $data[$field] = null;
            }
        }

        if ($imagePath) {
            $data['position_image'] = $imagePath;
        }

        if ($this->asset) {
            $this->asset->update($data);
        } else {
            $this->asset = Asset::create($data);
        }
    }
}
