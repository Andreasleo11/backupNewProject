<?php

namespace App\Livewire\Assets;

use App\Models\Asset;
use App\Models\AssetComponent;
use App\Models\AssetServiceRecord;
use App\Models\ComponentType;
use Livewire\Component;

class AssetShow extends Component
{
    public $asset;

    // Component Form Properties
    public $showComponentForm = false;
    public $editingComponentId = null;
    public $componentType = 'hardware';
    public $componentTypeName = '';
    public $componentBrand = '';
    public $componentName = '';
    public $componentSerialNumber = '';
    public $componentLicense = '';
    public $componentRemark = '';

    // Service Record Properties
    public $showServiceForm = false;
    public $editingServiceId = null;
    public $serviceRequestedBy = '';
    public $serviceAction = 'repair'; // replacement, installation, repair
    public $serviceComponentType = 'hardware';
    public $serviceOldPart = '';
    public $serviceNewTypeName = '';
    public $serviceNewBrand = '';
    public $serviceNewName = '';
    public $serviceNewSerialNumber = '';
    public $serviceNewLicense = '';
    public $serviceRemark = '';

    public function mount($id)
    {
        $this->loadAsset($id);
    }

    public function loadAsset($id)
    {
        $this->asset = Asset::with(['category', 'location', 'assignedTo', 'department', 'components', 'serviceRecords.performer'])->findOrFail($id);
    }

    public function render()
    {
        $componentTypes = ComponentType::all();
        return view('livewire.assets.asset-show', [
            'hardwareTypes' => $componentTypes->where('category', 'hardware'),
            'softwareTypes' => $componentTypes->where('category', 'software'),
        ]);
    }

    // Component CRUD
    public function resetComponentFields()
    {
        $this->componentType = 'hardware';
        $this->componentTypeName = '';
        $this->componentBrand = '';
        $this->componentName = '';
        $this->componentSerialNumber = '';
        $this->componentLicense = '';
        $this->componentRemark = '';
        $this->editingComponentId = null;
        $this->showComponentForm = false;
    }

    public function showAddComponentForm()
    {
        $this->resetComponentFields();
        $this->showComponentForm = true;
    }

    public function saveComponent()
    {
        $rules = [
            'componentType' => 'required|in:hardware,software',
            'componentTypeName' => 'required|string|max:255',
            'componentName' => 'required|string|max:255',
            'componentBrand' => 'nullable|string|max:255',
            'componentSerialNumber' => 'nullable|string|max:255',
            'componentRemark' => 'nullable|string|max:255',
        ];

        if ($this->componentType === 'software') {
            $rules['componentLicense'] = 'nullable|string|max:255';
        }

        $this->validate($rules);

        AssetComponent::updateOrCreate(
            ['id' => $this->editingComponentId],
            [
                'asset_id' => $this->asset->id,
                'component_type' => $this->componentType,
                'type_name' => $this->componentTypeName,
                'brand' => $this->componentBrand ?: null,
                'name' => $this->componentName,
                'serial_number' => $this->componentSerialNumber ?: null,
                'license' => $this->componentType === 'software' ? ($this->componentLicense ?: 'Not License') : null,
                'remark' => $this->componentRemark ?: null,
            ]
        );

        session()->flash('component_message', $this->editingComponentId ? 'Component updated.' : 'Component added.');
        $this->loadAsset($this->asset->id);
        $this->resetComponentFields();
    }

    public function editComponent($id)
    {
        $component = AssetComponent::findOrFail($id);
        $this->editingComponentId = $id;
        $this->componentType = $component->component_type;
        $this->componentTypeName = $component->type_name;
        $this->componentBrand = $component->brand;
        $this->componentName = $component->name;
        $this->componentSerialNumber = $component->serial_number;
        $this->componentLicense = $component->license;
        $this->componentRemark = $component->remark;
        $this->showComponentForm = true;
    }

    public function deleteComponent($id)
    {
        AssetComponent::findOrFail($id)->delete();
        session()->flash('component_message', 'Component deleted.');
        $this->loadAsset($this->asset->id);
    }

    // Service Record CRUD & Application
    public function resetServiceFields()
    {
        $this->serviceRequestedBy = '';
        $this->serviceAction = 'repair';
        $this->serviceComponentType = 'hardware';
        $this->serviceOldPart = '';
        $this->serviceNewTypeName = '';
        $this->serviceNewBrand = '';
        $this->serviceNewName = '';
        $this->serviceNewSerialNumber = '';
        $this->serviceNewLicense = '';
        $this->serviceRemark = '';
        $this->editingServiceId = null;
        $this->showServiceForm = false;
    }

    public function showAddServiceForm()
    {
        $this->resetServiceFields();
        $this->showServiceForm = true;
    }

    public function saveService()
    {
        $rules = [
            'serviceRequestedBy' => 'required|string|max:255',
            'serviceAction' => 'required|in:replacement,installation,repair',
        ];

        if ($this->serviceAction !== 'repair') {
            $rules['serviceComponentType'] = 'required|in:hardware,software';
            $rules['serviceNewTypeName'] = 'required|string|max:255';
            $rules['serviceNewName'] = 'required|string|max:255';
        }

        if ($this->serviceAction === 'replacement') {
            $rules['serviceOldPart'] = 'required|string|max:255';
        }

        $this->validate($rules);

        AssetServiceRecord::updateOrCreate(
            ['id' => $this->editingServiceId],
            [
                'asset_id' => $this->asset->id,
                'requested_by' => $this->serviceRequestedBy,
                'action' => $this->serviceAction,
                'component_type' => $this->serviceAction !== 'repair' ? $this->serviceComponentType : 'hardware',
                'old_part' => $this->serviceAction === 'replacement' ? $this->serviceOldPart : null,
                'new_type_name' => $this->serviceAction !== 'repair' ? $this->serviceNewTypeName : null,
                'new_brand' => $this->serviceAction !== 'repair' ? $this->serviceNewBrand : null,
                'new_name' => $this->serviceAction !== 'repair' ? $this->serviceNewName : null,
                'new_serial_number' => ($this->serviceAction !== 'repair' && $this->serviceComponentType === 'hardware') ? $this->serviceNewSerialNumber : null,
                'new_license' => ($this->serviceAction !== 'repair' && $this->serviceComponentType === 'software') ? $this->serviceNewLicense : null,
                'remark' => $this->serviceRemark ?: null,
            ]
        );

        session()->flash('service_message', $this->editingServiceId ? 'Service record updated.' : 'Service record created.');
        $this->loadAsset($this->asset->id);
        $this->resetServiceFields();
    }

    public function applyService($id)
    {
        $record = AssetServiceRecord::findOrFail($id);

        if ($record->action_date) {
            session()->flash('service_error', 'This service record has already been applied.');
            return;
        }

        \DB::transaction(function () use ($record) {
            if ($record->action === 'replacement') {
                $component = AssetComponent::where('asset_id', $record->asset_id)
                    ->where('component_type', $record->component_type)
                    ->where('name', $record->old_part)
                    ->first();

                if ($component) {
                    $component->update([
                        'type_name' => $record->new_type_name,
                        'brand' => $record->new_brand ?: $component->brand,
                        'name' => $record->new_name,
                        'serial_number' => $record->component_type === 'hardware' ? $record->new_serial_number : null,
                        'license' => $record->component_type === 'software' ? ($record->new_license ?: 'Not License') : null,
                        'remark' => $record->remark ?: $component->remark,
                    ]);
                } else {
                    AssetComponent::create([
                        'asset_id' => $record->asset_id,
                        'component_type' => $record->component_type,
                        'type_name' => $record->new_type_name,
                        'brand' => $record->new_brand,
                        'name' => $record->new_name,
                        'serial_number' => $record->new_serial_number,
                        'license' => $record->new_license ?: 'Not License',
                        'remark' => $record->remark,
                    ]);
                }
            } elseif ($record->action === 'installation') {
                AssetComponent::create([
                    'asset_id' => $record->asset_id,
                    'component_type' => $record->component_type,
                    'type_name' => $record->new_type_name,
                    'brand' => $record->new_brand,
                    'name' => $record->new_name,
                    'serial_number' => $record->new_serial_number,
                    'license' => $record->new_license ?: 'Not License',
                    'remark' => $record->remark,
                ]);
            }

            $record->update([
                'action_date' => now(),
                'performed_by' => auth()->id() ?: 1,
            ]);
        });

        session()->flash('service_message', 'Service record applied and component inventory updated.');
        $this->loadAsset($this->asset->id);
    }

    public function editService($id)
    {
        $record = AssetServiceRecord::findOrFail($id);
        $this->editingServiceId = $id;
        $this->serviceRequestedBy = $record->requested_by;
        $this->serviceAction = $record->action;
        $this->serviceComponentType = $record->component_type;
        $this->serviceOldPart = $record->old_part;
        $this->serviceNewTypeName = $record->new_type_name;
        $this->serviceNewBrand = $record->new_brand;
        $this->serviceNewName = $record->new_name;
        $this->serviceNewSerialNumber = $record->new_serial_number;
        $this->serviceNewLicense = $record->new_license;
        $this->serviceRemark = $record->remark;
        $this->showServiceForm = true;
    }

    public function deleteService($id)
    {
        AssetServiceRecord::findOrFail($id)->delete();
        session()->flash('service_message', 'Service record deleted.');
        $this->loadAsset($this->asset->id);
    }
}
