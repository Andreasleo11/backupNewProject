<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DetailSoftware;
use App\Models\SoftwareTypeInventory;
use App\Models\DetailHardware;
use App\Models\HardwareTypeInventory;
use App\Models\MasterInventory;
use App\Models\Department;

class MasterInventoryController extends Controller
{
    public function index()
    {
        $datas = MasterInventory::with([
            'hardwares.hardwareType',
            'softwares.softwareType' // Assuming you have a similar relationship for softwares
        ])->get();

        // dd($datas);
        return view('masterinventory.index', compact('datas'));
    }

    public function createpage()
    {
        $depts = Department::get();
        $hardwares = HardwareTypeInventory::get();
        $softwares = SoftwareTypeInventory::get();
        return view('masterinventory.create', compact('depts', 'hardwares', 'softwares'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

        // Validate the request
        $validatedData = $request->validate([
            'ip_address' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'dept' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'os' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'hardwares.*.type' => 'string|max:255',
            'hardwares.*.brand' => 'string|max:255',
            'hardwares.*.hardware_name' => 'string|max:255',
            'hardwares.*.remark' => 'nullable|string|max:255',
            'softwares.*.type' => 'string|max:255',
            'softwares.*.license' => 'string|max:255',
            'softwares.*.name' => 'string|max:255',
            'softwares.*.remark' => 'nullable|string|max:255',
            'position_image' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        if ($request->hasFile('position_image')) {
            $image = $request->file('position_image');
            $imageName = $image->getClientOriginalName();
            
            $imagePath = $image->storeAs('masterinventory', $imageName, 'public');
          
        }
        // Create the master inventory
        $masterInventory = MasterInventory::create([
            'ip_address' => $request->ip_address,
            'username' => $request->username,
            'position_image' => $imagePath,
            'dept' => $request->dept,
            'type' => $request->type,
            'purpose' => $request->purpose,
            'brand' => $request->brand,
            'os' => $request->os,
            'description' => $request->description,
        ]);

        // Store hardwares
        if ($request->has('hardwares')) {
            foreach ($request->hardwares as $hardware) {
                $masterInventory->hardwares()->create([
                    'hardware_id' => $hardware['type'],
                    'brand' => $hardware['brand'],
                    'hardware_name' => $hardware['hardware_name'],
                    'remark' => $hardware['remark'],
                ]);
            }
        }

        // Store softwares
        if ($request->has('softwares')) {
            foreach ($request->softwares as $software) {
                $masterInventory->softwares()->create([
                    'software_id' => $software['type'],
                    'license' => $software['license'],
                    'software_name' => $software['software_name'],
                    'remark' => $software['remark'],
                ]);
            }
        }

        return redirect()->route('masterinventory.index')->with('success', 'Master Inventory created successfully.');
    }

    public function editpage($id)
    {
        $data = MasterInventory::with([
            'hardwares.hardwareType',
            'softwares.softwareType'
        ])->findOrFail($id);

        $depts = Department::get();
        $hardwareTypes = HardwareTypeInventory::get();
        $softwareTypes = SoftwareTypeInventory::get();
    
        return view('masterinventory.edit', compact('data', 'depts', 'hardwareTypes', 'softwareTypes'));
    }


    public function update(Request $request, $id)
    {
        //    dd($request->all());
        $validatedData = $request->validate([
            'ip_address' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'dept' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'os' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'hardwares.*.brand' => 'nullable|string|max:255',
            'hardwares.*.hardware_name' => 'nullable|string|max:255',
            'hardwares.*.remark' => 'nullable|string|max:255',
            'softwares.*.software_name' => 'nullable|string|max:255',
            'softwares.*.license' => 'nullable|string|max:255',
            'softwares.*.remark' => 'nullable|string|max:255',
            'position_image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $masterInventory = MasterInventory::findOrFail($id);

         // Handle the image update
        if ($request->hasFile('position_image')) {
            // Delete the old image if it exists
            if ($masterInventory->position_image) {
                $oldImagePath = public_path('storage/' . $masterInventory->position_image);
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $image = $request->file('position_image');
            $imageName = $image->getClientOriginalName();
            // Store the new image and update the model
            $imagePath = $imagePath = $image->storeAs('masterinventory', $imageName, 'public');
            $masterInventory->position_image = $imagePath;
        }


        $masterInventory->update([
            'ip_address' => $request->ip_address,
            'username' => $request->username,
            'dept' => $request->dept,
            'type' => $request->type,
            'purpose' => $request->purpose,
            'brand' => $request->brand,
            'os' => $request->os,
            'description' => $request->description,
        ]);

       // Updating Hardwares
        $existingHardwareIds = $masterInventory->hardwares->pluck('id')->toArray();
        $newHardwareIds = [];

        if ($request->has('hardwares')) {
            foreach ($request->hardwares as $hardware) {
                if (!empty($hardware['brand']) && !empty($hardware['hardware_name'])) {
                    // Try to find an existing hardware by unique fields (e.g., hardware_name and brand)
                    $existingHardware = $masterInventory->hardwares()
                        ->where('hardware_name', $hardware['hardware_name'])
                        ->where('brand', $hardware['brand'])
                        ->first();

                    if ($existingHardware) {
                        $existingHardware->update([
                            'hardware_id' => $hardware['type'],
                            'brand' => $hardware['brand'],
                            'hardware_name' => $hardware['hardware_name'],
                            'remark' => $hardware['remark'],
                        ]);
                        $newHardwareIds[] = $existingHardware->id;
                    } else {
                        $newHardware = $masterInventory->hardwares()->create([
                            'master_inventory_id' => $masterInventory->id,
                            'hardware_id' => $hardware['type'],
                            'brand' => $hardware['brand'],
                            'hardware_name' => $hardware['hardware_name'],
                            'remark' => $hardware['remark'],
                        ]);
                        $newHardwareIds[] = $newHardware->id;
                    }
                }
            }
        }

        // Remove deleted hardwares
        $toBeDeletedHardwareIds = array_diff($existingHardwareIds, $newHardwareIds);
        if (!empty($toBeDeletedHardwareIds)) {
            $masterInventory->hardwares()->whereIn('id', $toBeDeletedHardwareIds)->delete();
        }

        // Updating Softwares
        $existingSoftwareIds = $masterInventory->softwares->pluck('id')->toArray();
        $newSoftwareIds = [];

        if ($request->has('softwares')) {
            foreach ($request->softwares as $software) {
                if (!empty($software['software_name']) && !empty($software['license'])) {
                    // Try to find an existing software by unique fields (e.g., software_name and license)
                    $existingSoftware = $masterInventory->softwares()
                        ->where('software_name', $software['software_name'])
                        ->where('license', $software['license'])
                        ->first();

                    if ($existingSoftware) {
                        $existingSoftware->update([
                            'software_id' => $software['type'],
                            'license' => $software['license'],
                            'software_name' => $software['software_name'],
                            'remark' => $software['remark'],
                        ]);
                        $newSoftwareIds[] = $existingSoftware->id;
                    } else {
                        $newSoftware = $masterInventory->softwares()->create([
                            'master_inventory_id' => $masterInventory->id,
                            'software_id' => $software['type'],
                            'license' => $software['license'],
                            'software_name' => $software['software_name'],
                            'remark' => $software['remark'],
                        ]);
                        $newSoftwareIds[] = $newSoftware->id;
                    }
                }
            }
        }

        // Remove deleted softwares
        $toBeDeletedSoftwareIds = array_diff($existingSoftwareIds, $newSoftwareIds);
        if (!empty($toBeDeletedSoftwareIds)) {
            $masterInventory->softwares()->whereIn('id', $toBeDeletedSoftwareIds)->delete();
        }

        return redirect()->route('masterinventory.index')->with('success', 'Master Inventory updated successfully.');
    }


    public function detail($id)
    {
        $data = MasterInventory::with([
            'hardwares.hardwareType',
            'softwares.softwareType'
        ])->findOrFail($id);

        $depts = Department::get();
        $hardwareTypes = HardwareTypeInventory::get();
        $softwareTypes = SoftwareTypeInventory::get();

        return view('masterinventory.detail', compact('data', 'depts', 'hardwareTypes', 'softwareTypes'));
    }
}
