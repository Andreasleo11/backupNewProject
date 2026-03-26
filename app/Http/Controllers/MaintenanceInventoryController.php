<?php

namespace App\Http\Controllers;

use App\Domain\Inventory\Services\MaintenanceReportService;
use App\Http\Requests\StoreMaintenanceInventoryRequest;
use App\Http\Requests\UpdateMaintenanceInventoryRequest;
use App\Models\DetailMaintenanceInventoryReport;
use App\Models\GroupMaintenanceInventoryReport;
use App\Models\HeaderMaintenanceInventoryReport;
use App\Models\MasterInventory;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceInventoryController extends Controller
{
    public function __construct(
        private readonly MaintenanceReportService $reportService
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'periode' => $request->input('periode'),
            'year' => $request->input('year', date('Y')),
        ];

        $data = $this->reportService->getReports($filters);
        $reports = $data['reports'];
        $headerData = $data['headerData'];

        $masterIdsInReports = $headerData->pluck('master_id')->unique();
        $masterInventories = MasterInventory::all();
        $usernameStatuses = [];

        foreach ($masterInventories as $inventory) {
            $usernameStatuses[$inventory->username] = $masterIdsInReports->contains($inventory->id) ? 'yes' : 'no';
        }

        return view('maintenance-inventory.index', array_merge($filters, compact('reports', 'usernameStatuses')));
    }

    public function create($id = null)
    {
        $periodeCaturwulan = $this->reportService->getPeriodeCaturwulan(now()->month);
        $existingHeader = HeaderMaintenanceInventoryReport::where('periode_caturwulan', $periodeCaturwulan)
            ->where('master_id', $id)
            ->latest('created_at')
            ->first();

        if ($existingHeader) {
            return redirect()->route('maintenance.inventory.show', ['id' => $existingHeader->id]);
        }

        $masters = MasterInventory::all();
        $users = User::whereIn('name', ['vicky', 'bagus'])->get();
        $groups = GroupMaintenanceInventoryReport::with('detail')->get()->map(fn ($group) => [
            'group_id' => $group->id,
            'group_name' => $group->name,
            'details' => $group->detail->map(fn ($d) => ['id' => $d->id, 'name' => $d->name])->toArray(),
        ])->toArray();

        return view('maintenance-inventory.create', compact('masters', 'users', 'groups', 'id'));
    }

    public function store(StoreMaintenanceInventoryRequest $request)
    {
        $this->reportService->createReport($request->all());

        return redirect()->route($request->action === 'create_another' ? 'maintenance.inventory.create' : 'maintenance.inventory.index')
            ->with('success', 'Maintenance Inventory Report created successfully!');
    }

    public function show($id)
    {
        $report = HeaderMaintenanceInventoryReport::with('detail', 'detail.typecategory')->findOrFail($id);

        return view('maintenance-inventory.detail', compact('report'));
    }

    public function edit($id)
    {
        $masters = MasterInventory::all();
        $users = User::whereIn('name', ['vicky', 'bagus'])->get();
        $details = DetailMaintenanceInventoryReport::with('typecategory', 'typecategory.group')->where('header_id', $id)->get();
        $groupedDetails = $details->groupBy(fn ($item) => $item->typecategory->group->name);
        $report = HeaderMaintenanceInventoryReport::with('detail')->findOrFail($id);

        return view('maintenance-inventory.edit', compact('report', 'masters', 'users', 'groupedDetails'));
    }

    public function update(UpdateMaintenanceInventoryRequest $request, $id)
    {
        $this->reportService->updateReport((int) $id, $request->all());

        return redirect()->back()->with('success', 'Maintenance Inventory Report successfully updated!');
    }
}
