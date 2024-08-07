<?php

namespace App\Http\Controllers;

use App\Models\HeaderMaintenanceInventoryReport;
use App\Models\GroupMaintenanceInventoryReport;
use App\Models\DetailMaintenanceInventoryReport;
use App\Models\CategoryMaintenanceInventoryReport;
use App\Models\MasterInventory;
use Illuminate\Http\Request;

class MaintenanceInventoryController extends Controller
{
    public function index()
    {
        $reports = HeaderMaintenanceInventoryReport::all();
        return view('maintenance-inventory.index', compact('reports'));
    }

    public function createpage()
    {
        $masters = MasterInventory::all();

        return view('maintenance-inventory.createpage', compact('masters'));
    }

    public function createprocess(Request $request)
    {
        // dd($request->all());
        $masterid = $request->master_id;
        // dd($masterid);
        $master = MasterInventory::with([
            'hardwares.hardwareType',
            'softwares.softwareType' // Assuming you have a similar relationship for softwares
        ])->find($masterid);
        // dd($master);

        $groups = GroupMaintenanceInventoryReport::with('detail')->get();
        
        $transformedGroups = $groups->map(function ($group) {
            return [
                $group->name => $group->detail->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'name' => $detail->name,
                    ];
                })->toArray(),
            ];
        })->toArray();

        dd($transformedGroups);
    
        // Output the transformed data for debugging
        return view('maintenance-inventory.createindex', compact('master', 'transformedGroups', 'masterid'));
    }

}
