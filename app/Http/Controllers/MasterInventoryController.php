<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Services\InventoryExportService;
use App\Domain\Inventory\Services\InventoryManagementService;
use App\Domain\Inventory\Services\InventoryRepairService;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Models\DetailHardware;
use App\Models\DetailSoftware;
use App\Models\HardwareTypeInventory;
use App\Models\HeaderMaintenanceInventoryReport;
use App\Models\InventoryRepairHistory;
use App\Models\MasterInventory;
use App\Models\SoftwareTypeInventory;
use Illuminate\Http\Request;

class MasterInventoryController extends BaseController
{
    public function __construct(
        private readonly InventoryManagementService $inventoryService,
        private readonly InventoryRepairService $repairService,
        private readonly InventoryExportService $exportService
    ) {}

    public function index(Request $request)
    {
        $itemsPerPage = $request->get('itemsPerPage', 10);
        $validColumns = ['ip_address', 'username', 'dept', 'type', 'purpose', 'brand', 'os', 'description'];

        $filters = [];
        foreach ($validColumns as $column) {
            if ($request->filled($column)) {
                $filters[$column] = $request->get($column);
            }
        }

        $datas = $this->inventoryService->getInventoryItems($filters, $itemsPerPage);

        return view('masterinventory.index', compact('datas'));
    }

    public function createpage()
    {
        $depts = Department::all();
        $hardwares = HardwareTypeInventory::all();
        $softwares = SoftwareTypeInventory::all();

        return view('masterinventory.create', compact('depts', 'hardwares', 'softwares'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'dept' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'os' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'position_image' => 'required|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $this->inventoryService->storeInventory($request->all(), $request->file('position_image'));

        return redirect()->route('masterinventory.index')->with('success', 'Master Inventory created successfully.');
    }

    public function editpage($id)
    {
        $data = MasterInventory::with(['hardwares.hardwareType', 'softwares.softwareType'])->findOrFail($id);
        $depts = Department::all();
        $hardwareTypes = HardwareTypeInventory::all();
        $softwareTypes = SoftwareTypeInventory::all();

        return view('masterinventory.edit', compact('data', 'depts', 'hardwareTypes', 'softwareTypes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'ip_address' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'dept' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'purpose' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'os' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'position_image' => 'nullable|image|mimes:jpg,png,jpeg,gif|max:2048',
        ]);

        $this->inventoryService->updateInventory($id, $request->all(), $request->file('position_image'));

        return redirect()->route('masterinventory.index')->with('success', 'Master Inventory updated successfully.');
    }

    public function detail($id)
    {
        $data = MasterInventory::with(['hardwares.hardwareType', 'softwares.softwareType'])->findOrFail($id);
        $depts = Department::all();
        $hardwareTypes = HardwareTypeInventory::all();
        $softwareTypes = SoftwareTypeInventory::all();
        $repairHistories = InventoryRepairHistory::where('master_id', $id)->get();
        $inventoryHistories = HeaderMaintenanceInventoryReport::with('detail', 'detail.typecategory')->where('master_id', $id)->get();

        $processedHistories = $repairHistories->map(function ($repairHistory) {
            $repairHistory->typeDetails = ($repairHistory->type === 'hardware') ? $repairHistory->hardwareType : $repairHistory->softwareType;

            return $repairHistory;
        });

        return view('masterinventory.detail', compact('data', 'depts', 'hardwareTypes', 'softwareTypes', 'processedHistories', 'inventoryHistories'));
    }

    public function typeAdder()
    {
        $hardwareTypes = HardwareTypeInventory::all();
        $softwareTypes = SoftwareTypeInventory::all();

        return view('masterinventory.typeadd', compact('hardwareTypes', 'softwareTypes'));
    }

    public function addHardwareType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $this->inventoryService->addHardwareType($request->name);

        return response()->json(['success' => true]);
    }

    public function addSoftwareType(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $this->inventoryService->addSoftwareType($request->name);

        return response()->json(['success' => true]);
    }

    public function deleteType(Request $request)
    {
        $request->validate(['id' => 'required|integer', 'type' => 'required|string|in:hardware,software']);
        $success = $this->inventoryService->deleteType($request->id, $request->type);

        return response()->json(['success' => $success]);
    }

    public function getItems($type)
    {
        $items = ($type === 'hardware') ? HardwareTypeInventory::all() : SoftwareTypeInventory::all();

        return response()->json($items);
    }

    public function getAvailableItems(Request $request)
    {
        $items = ($request->query('type') === 'hardware')
            ? DetailHardware::where('master_inventory_id', $request->query('master_id'))->get(['id', 'hardware_name as name'])
            : DetailSoftware::where('master_inventory_id', $request->query('master_id'))->get(['id', 'software_name as name']);

        return response()->json($items);
    }

    public function CreateRepair(Request $request)
    {
        $request->validate(['master_id' => 'required', 'requestName' => 'required|string', 'type' => 'required|string', 'action' => 'required|string']);
        $repair = $this->repairService->createRepair($request->all());

        return redirect()->route('masterinventory.detail', ['id' => $request->master_id])->with('success', 'Data inserted successfully');
    }

    public function updateHistory($id)
    {
        $this->repairService->applyRepairHistory($id);

        return redirect()->back()->with('success', 'Inventory updated or added successfully.');
    }

    public function destroy($id)
    {
        $this->inventoryService->deleteInventory($id);

        return redirect()->route('masterinventory.index')->with('success', 'Inventory Master deleted successfully!');
    }

    public function export()
    {
        return $this->exportService->downloadExport();
    }

    public function generateQr($id)
    {
        $result = $this->exportService->generateQrCode($id);

        return view('masterinventory.qrcode', $result);
    }
}
