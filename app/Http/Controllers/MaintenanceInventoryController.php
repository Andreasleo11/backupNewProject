<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceInventoryRequest;
use App\Http\Requests\UpdateMaintenanceInventoryRequest;
use App\Models\HeaderMaintenanceInventoryReport;
use App\Models\GroupMaintenanceInventoryReport;
use App\Models\DetailMaintenanceInventoryReport;
use App\Models\CategoryMaintenanceInventoryReport;
use App\Models\MasterInventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaintenanceInventoryController extends Controller
{
    public function index()
    {
        $reportsQuery = HeaderMaintenanceInventoryReport::query();
        $reports = $reportsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('maintenance-inventory.index', compact('reports'));
    }

    public function create($id = null)
    {
        $masters = MasterInventory::all();
        $users = User::where(function ($query) {
            $query->where('name', 'vicky')->orWhere('name', 'bagus');
        })->get();
        $groups = GroupMaintenanceInventoryReport::with('detail')->get()->map(function ($group) {
            return [
                'group_id' => $group->id, // Add group_id here
                'group_name' => $group->name,
                'details' => $group->detail->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'name' => $detail->name,
                    ];
                })->toArray(),
            ];
        })->toArray();
        // dd($groups);

        return view('maintenance-inventory.create', compact('masters', 'users', 'groups', 'id'));
    }

    public function store(StoreMaintenanceInventoryRequest $request)
    {
        // dd($request->all());
        $header = $this->createHeader($request);

        $this->createDetails($request, $header->id);
        $this->createNewItems($request, $header->id);

        return $this->handleRedirect($request);
    }

    private function createHeader(StoreMaintenanceInventoryRequest $request)
    {
        return HeaderMaintenanceInventoryReport::create([
            'no_dokumen' => 'MIR-' . now()->timestamp,
            'master_id' => $request->input('master_id'),
            'revision_date' => $request->input('revision_date'),
        ]);
    }

    private function createDetails(StoreMaintenanceInventoryRequest $request, $headerId)
    {
        $items = $request->input('items', []);
        foreach ($items as $itemId) {
            DetailMaintenanceInventoryReport::create([
                'header_id' => $headerId,
                'category_id' => $itemId,
                'condition' => $request->input("conditions.$itemId"),
                'remark' => $request->input("remarks.$itemId"),
                'checked_by' => $request->input("checked_by.$itemId"),
            ]);
        }
    }

    private function createNewItems(StoreMaintenanceInventoryRequest $request, $headerId)
    {
        $newItems = $request->input('new_items', []);
        foreach ($newItems as $index => $newItemId) {
            $newItemName = $request->input("new_items_names.$newItemId");
            if (!$newItemName) {
                continue;
            }

            // Create a new CategoryMaintenanceInventoryReport
            $category = CategoryMaintenanceInventoryReport::create([
                'group_id' => $request->input("new_group_ids.$newItemId"), // Assuming you are passing group_id for new items
                'name' => $newItemName,
            ]);

            // Create a new DetailMaintenanceInventoryReport with the new category_id
            DetailMaintenanceInventoryReport::create([
                'header_id' => $headerId,
                'category_id' => $category->id,
                'condition' => $request->input("new_conditions.$newItemId"),
                'remark' => $request->input("new_remarks.$newItemId"),
                'checked_by' => $request->input("new_checked_by.$newItemId"),
            ]);
        }
    }

    private function handleRedirect(StoreMaintenanceInventoryRequest $request)
    {
        $action = $request->input('action');
        if ($action === 'create_another') {
            return redirect()->route('maintenance.inventory.create')->with('success', 'Maintenance Inventory Report created successfully!')->withInput();
        } else {
            return redirect()->route('maintenance.inventory.index')->with('success', 'Maintenance Inventory Report created successfully!');
        }
    }

    public function show($id)
    {
        $report = HeaderMaintenanceInventoryReport::with('detail')->find($id);
        return view('maintenance-inventory.detail', compact('report'));
    }

    public function edit($id)
    {
        $masters = MasterInventory::all();
        $users = User::where(function ($query) {
            $query->where('name', 'vicky')->orWhere('name', 'bagus');
        })->get();
        $details = DetailMaintenanceInventoryReport::with('typecategory', 'typecategory.group')->where('header_id', $id)->get();
        $groupedDetails = $details->groupBy(function ($item) {
            return $item->typecategory->group->name;
        });
        $report = HeaderMaintenanceInventoryReport::with('detail')->find($id);
        return view('maintenance-inventory.edit', compact('report', 'masters', 'users', 'groupedDetails'));
    }

    public function update(UpdateMaintenanceInventoryRequest $request, $id)
    {
        // dd($request->all());
        $header = HeaderMaintenanceInventoryReport::findOrFail($id);

        $header->update([
            'master_id' => $request->input('master_id'),
            'revision_date' => $request->input('revision_date'),
        ]);

        $this->updateDetails($request, $header->id);

        // Handle new items
        if ($request->has('new_items')) {
            foreach ($request->input('new_items') as $newItemId) {
                DetailMaintenanceInventoryReport::create([
                    'header_id' => $header->id,
                    'name' => $request->input("new_items_names.$newItemId"),
                    'condition' => $request->input("new_conditions.$newItemId"),
                    'remark' => $request->input("new_remarks.$newItemId"),
                    'checked_by' => $request->input("new_checked_by.$newItemId"),
                    'group_id' => $request->input("new_group_ids.$newItemId"),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Maintenance Inventory Report successfully updated!');
    }

    private function updateDetails(UpdateMaintenanceInventoryRequest $request, $headerId)
    {
        $items = $request->input('items', []);
        foreach ($items as $itemId) {
            $detail = DetailMaintenanceInventoryReport::find($itemId);
            if ($detail) {
                $detail->update([
                    'condition' => $request->input("conditions.$itemId"),
                    'remark' => $request->input("remarks.$itemId"),
                    'checked_by' => $request->input("checked_by.$itemId"),
                ]);
            }
        }
    }
}
