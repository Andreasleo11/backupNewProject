<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Facades\Log;
use App\Models\DetailSoftware;
use App\Models\SoftwareTypeInventory;
use App\Models\DetailHardware;
use App\Models\HardwareTypeInventory;
use App\Models\MasterInventory;
use App\Models\Department;
use App\Models\InventoryRepairHistory;
use App\Models\HeaderMaintenanceInventoryReport;
use App\Models\DetailMaintenanceInventoryReport;
use App\Exports\InventoryMasterExport;
use Maatwebsite\Excel\Facades\Excel;

class MasterInventoryController extends BaseController
{
    public function index(Request $request)
    {
        $itemsPerPage = $request->get('itemsPerPage', 10); // Default to 10 items per page

        $query = MasterInventory::with([
            'hardwares.hardwareType',
            'softwares.softwareType' // Assuming you have a similar relationship for softwares
        ]);

        // Apply filters if any
        $validColumns = ['ip_address', 'username', 'dept', 'type', 'purpose', 'brand', 'os', 'description'];
        $query = $this->applyFilters($query, $request, $validColumns);

        // Apply pagination or get all items
        if ($itemsPerPage == 'all') {
            $datas = $query->get(); // Get all items without pagination
        } else {
            $datas = $query->paginate($itemsPerPage);
        }

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
            'softwares.*.brand' => 'string|max:255',
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
                    'software_brand' => $software['software_brand'],
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
            'softwares.*.brand' => 'string|max:255',
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
                            'software_brand' => $software['software_brand'],
                            'license' => $software['license'],
                            'software_name' => $software['software_name'],
                            'remark' => $software['remark'],
                        ]);
                        $newSoftwareIds[] = $existingSoftware->id;
                    } else {
                        $newSoftware = $masterInventory->softwares()->create([
                            'master_inventory_id' => $masterInventory->id,
                            'software_id' => $software['type'],
                            'software_brand' => $software['software_brand'],
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
        $repairHistories = InventoryRepairHistory::where('master_id', $id)->get();

        $inventoryHistories = HeaderMaintenanceInventoryReport::with('detail', 'detail.typecategory')->where('master_id',$id)->get();
        
        $processedHistories = $repairHistories->map(function ($repairHistory) {
            if ($repairHistory->type === 'hardware') {
                $repairHistory->typeDetails = $repairHistory->hardwareType; // Attach hardwareType details
            } elseif ($repairHistory->type === 'software') {
                $repairHistory->typeDetails = $repairHistory->softwareType; // Attach softwareType details
            }
            return $repairHistory;
        });

        // dd($processedHistories);
        return view('masterinventory.detail', compact('data', 'depts', 'hardwareTypes', 'softwareTypes', 'processedHistories', 'inventoryHistories'));
    }


    public function typeAdder()
    {
        $masterId = 10;
        $items = DetailHardware::where('master_inventory_id', $masterId)->get([
            'hardware_id as id',
            'hardware_name as name'
        ]);
       
        $hardwareTypes = HardwareTypeInventory::all();
        $softwareTypes = SoftwareTypeInventory::all();

        return view('masterinventory.typeadd', compact('hardwareTypes', 'softwareTypes'));
    }


    public function addHardwareType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $type = new HardwareTypeInventory();
        $type->name = $request->input('name');
        $type->save();

        return response()->json(['success' => true]);
    }

    public function addSoftwareType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $type = new SoftwareTypeInventory();
        $type->name = $request->input('name');
        $type->save();

        return response()->json(['success' => true]);
    }

    public function deleteType(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'type' => 'required|string|in:hardware,software'
        ]);

        if ($request->input('type') == 'hardware') {
            $type = HardwareTypeInventory::find($request->input('id'));
        } elseif ($request->input('type') == 'software') {
            $type = SoftwareTypeInventory::find($request->input('id'));
        } else {
            return response()->json(['success' => false, 'message' => 'Invalid type'], 400);
        }

        if ($type) {
            $type->delete();
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'message' => 'Type not found'], 404);
        }
    }


    public function getItems($type)
    {
        $items = [];

        if ($type === 'hardware') {
            $items = HardwareTypeInventory::all(); // Fetch hardware types
        } elseif ($type === 'software') {
            $items = SoftwareTypeInventory::all(); // Fetch software types
        }

        return response()->json($items);
    }

    public function getAvailableItems(Request $request)
    {
        $type = $request->query('type');
        $masterId = $request->query('master_id');


        $items = [];
        if (!$type || !$masterId) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }

        // Fetch items based on type
        if ($type === 'hardware') {
            $items = DetailHardware::where('master_inventory_id', $masterId)
                ->get(['id as id', 'hardware_name as name']);
        } elseif ($type === 'software') {
            $items = DetailSoftware::where('master_inventory_id', $masterId)
                ->get(['id as id', 'software_name as name']);
        } else {
            return response()->json(['error' => 'Invalid type specified'], 400);
        }

        return response()->json($items);
    }

    public function CreateRepair(Request $request)
    {
        // dd($request->all());
        // Validate the request
        $validated = $request->validate([
            '_token' => 'required',
            'master_id' => 'required',
            'requestName' => 'required|string',
            'type' => 'required|string',
            'action' => 'required|string',
            'oldPart' => 'nullable|string',
            'itemType' => 'nullable|string',
            'itemBrand' => 'nullable|string',
            'itemName' => 'nullable|string',
            'itemTypeInstallation' => 'nullable|string',
            'itemBrandInstallation' => 'nullable|string',
            'itemNameInstallation' => 'nullable|string',
            'remark' => 'nullable|string',
        ]);

        // Prepare data for insertion
        $data = [
            'master_id' => $validated['master_id'],
            'request_name' => $validated['requestName'],
            'type' => $validated['type'],
            'action' => $validated['action'],
            'old_part' => $validated['oldPart'],
            'remark' => $validated['remark'],
        ];


        if ($validated['action'] === 'replacement') {
            $data['item_type'] = $validated['itemType'];
            $data['item_brand'] = $validated['itemBrand'];
            $data['item_name'] = $validated['itemName'];
        } elseif ($validated['action'] === 'installation') {
            $data['item_type'] = $validated['itemTypeInstallation'];
            $data['item_brand'] = $validated['itemBrandInstallation'];
            $data['item_name'] = $validated['itemNameInstallation'];
        }

        // dd($data);

        // Insert data into InventoryRepairHistory
        InventoryRepairHistory::create($data);

        // Return a response or redirect
        return redirect()->route('masterinventory.detail', ['id' => $validated['master_id']])
            ->with('success', 'Data inserted successfully');
    }


    public function updateHistory($id)
    {
        // Find the InventoryRepairHistory record by id
        $repairHistory = InventoryRepairHistory::findOrFail($id);
        // dd($repairHistory);
        // Your logic to update or add data into the masterinventory table
        // Example logic:
        $inventory = MasterInventory::where('id', $repairHistory->master_id)->with([
            'hardwares.hardwareType',
            'softwares.softwareType' // Assuming you have a similar relationship for softwares
        ])->first();

        $repairHistory->action_date = now(); // Set action_date to the current date and time
        $repairHistory->save(); // Save the changes

        if ($repairHistory->action === 'replacement') {
            // Handle replacement logic
            if ($repairHistory->type === 'hardware') {
                // Find the hardware detail to update
                $hardwareDetail = $inventory->hardwares->firstWhere('hardware_name', $repairHistory->old_part);

                if ($hardwareDetail) {
                    // Update the hardware detail with new values
                    $hardwareDetail->update([
                        'hardware_name' => $repairHistory->item_name, // Example of updating hardware name
                        'brand' => $repairHistory->item_brand, // Example of updating hardware brand
                        'remark' => $repairHistory->remark,
                        // Add more fields if needed
                    ]);
                } else {
                    // Handle case where hardware detail is not found
                    return response()->json(['message' => 'Hardware detail not found'], 404);
                }
            } elseif ($repairHistory->type === 'software') {
                // Find the software detail to update
                $softwareDetail = $inventory->softwares->firstWhere('software_name', $repairHistory->old_part);
                if ($softwareDetail) {
                    // Update the software detail with new values
                    $softwareDetail->update([
                        'software_name' => $repairHistory->item_name, // Example of updating software name
                        'software_brand' => $repairHistory->item_brand, // Example of updating software brand
                        'remark' => $repairHistory->remark,
                        // Add more fields if needed
                    ]);
                } else {
                    // Handle case where software detail is not found
                    return response()->json(['message' => 'Software detail not found'], 404);
                }
            }
        } elseif ($repairHistory->action === 'installation') {
            // Handle installation logic
            if ($repairHistory->type === 'hardware') {
                // Create new hardware detail
                $inventory->hardwares()->create([
                    'master_inventory_id' => $repairHistory->master_id,
                    'hardware_id' => $repairHistory->item_type,
                    'hardware_name' => $repairHistory->item_name,
                    'brand' => $repairHistory->item_brand,
                    'remark' => $repairHistory->remark, // Example relationship
                    // Add more fields if needed
                ]);
            } elseif ($repairHistory->type === 'software') {
                // Create new software detail
                $inventory->softwares()->create([
                    'master_inventory_id' => $repairHistory->master_id,
                    'software_name' => $repairHistory->item_name,
                    'software_brand' => $repairHistory->item_brand,
                    'software_id' => $repairHistory->item_type,
                    'remark' => $repairHistory->remark,
                    'license' => 'Not License', // Example relationship
                    // Add more fields if needed
                ]);
            }
        } else {
            // Handle invalid action
            return response()->json(['message' => 'Invalid action'], 400);
        }

        // Redirect or return response
        return redirect()->back()->with('success', 'Inventory updated or added successfully.');
    }


    public function destroy($id)
    {
        // Find the inventory master record
        $inventoryMaster = MasterInventory::findOrFail($id);

        // Delete related DetailHardware records
        DetailHardware::where('master_inventory_id', $id)->delete();

        // Delete related DetailSoftware records
        DetailSoftware::where('master_inventory_id', $id)->delete();

        $headerReports = HeaderMaintenanceInventoryReport::where('master_id', $id)->get();

        // Delete related DetailMaintenanceInventoryReport records
        foreach ($headerReports as $headerReport) {
            DetailMaintenanceInventoryReport::where('header_id', $headerReport->id)->delete();
        }

        // Delete the header maintenance inventory reports
        HeaderMaintenanceInventoryReport::where('master_id', $id)->delete();

        // Delete the inventory master record
        $inventoryMaster->delete();

        // Redirect back with a success message
        return redirect()->route('masterinventory.index')->with('success', 'Inventory Master deleted successfully!');
    }

    public function export()
    {
        return Excel::download(new InventoryMasterExport, 'listKomputer.xlsx');
    }


    public function generateQr($id)
    {
        $data = DetailHardware::with('masterInventory','hardwareType')->find($id);
       
        $qrData = $data->brand . '~' . $data->hardwareType->name . '~' . $data->hardware_name;
        dd($qrData);
    }
}
