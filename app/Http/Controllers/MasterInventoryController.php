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
            'hardwares.*.type' => 'string|max:255',
            'hardwares.*.brand' => 'string|max:255',
            'hardwares.*.hardware_name' => 'string|max:255',
            'hardwares.*.remark' => 'nullable|string|max:255',
            'softwares.*.type' => 'string|max:255',
            'softwares.*.license' => 'string|max:255',
            'softwares.*.name' => 'string|max:255',
            'softwares.*.remark' => 'nullable|string|max:255',
        ]);

        // Create the master inventory
        $masterInventory = MasterInventory::create([
            'ip_address' => $request->ip_address,
            'username' => $request->username,
            'dept' => $request->dept,
            'type' => $request->type,
            'purpose' => $request->purpose,
            'brand' => $request->brand,
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
    
}