<?php

namespace App\Http\Controllers;

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
        $reports = HeaderMaintenanceInventoryReport::all();
        return view('maintenance-inventory.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $masters = MasterInventory::all();
        $users = User::where(function ($query) {
            $query->where('name', 'vicky')->orWhere('name', 'bagus');
        })->get();
        $groups = GroupMaintenanceInventoryReport::with('detail')->get()->map(function ($group) {
            return [
                $group->name => $group->detail->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'name' => $detail->name,
                    ];
                })->toArray(),
            ];
        })->toArray();

        // dd($transformedGroups);

        return view('maintenance-inventory.create', compact('masters', 'users', 'groups'));
    }

    public function store(Request $request)
    {
        // Validation rules
        $rules = [
            'master_id' => 'required|exists:master_inventories,id',
            'revision_date' => 'nullable|date',
            'items' => 'required|array',
            'items.*' => 'exists:category_maintenance_inventory_reports,id',
            'conditions' => 'required|array',
            'conditions.*' => 'in:good,bad',
            'remarks' => 'required|array',
            'checked_by' => 'required|array',
            'checked_by.*' => 'exists:users,name',
            'new_items' => 'array',
            'new_items_names' => 'array',
            'new_items_names.*' => 'required_with:new_items',
            'new_conditions' => 'array',
            'new_conditions.*' => 'in:good,bad',
            'new_remarks' => 'array',
        ];

        // Validation messages
        $messages = [
            'master_id.required' => 'The master inventory is required.',
            'master_id.exists' => 'The selected master inventory does not exist.',
            'revision_date.date' => 'The revision date is not a valid date.',
            'items.required' => 'At least one item must be selected.',
            'items.*.exists' => 'One or more selected items do not exist.',
            'conditions.required' => 'Conditions are required.',
            'conditions.*.in' => 'Each condition must be either good or bad.',
            'remarks.required' => 'Remarks are required.',
            'checked_by.required' => 'Checked by field is required.',
            'checked_by.*.exists' => 'The selected checker does not exist.',
            'new_items_names.*.required_with' => 'The name for each new item is required.',
            'new_conditions.*.in' => 'Each new condition must be either good or bad.',
        ];

        // Validate the request data
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create the header
        $header = HeaderMaintenanceInventoryReport::create([
            'no_dokumen' => 'MIR-' . now()->timestamp, // or any other document number generation logic
            'master_id' => $request->input('master_id'),
            'revision_date' => $request->input('revision_date'),
        ]);

        // Create the details
        $items = $request->input('items', []);
        foreach ($items as $itemId) {
            DetailMaintenanceInventoryReport::create([
                'header_id' => $header->id,
                'category_id' => $itemId,
                'condition' => $request->input("conditions.$itemId"),
                'remark' => $request->input("remarks.$itemId"),
                'checked_by' => $request->input("checked_by.$itemId"),
            ]);
        }

        // Handle new items
        $newItems = $request->input('new_items', []);
        foreach ($newItems as $index => $newItemId) {
            $newItemName = $request->input("new_items_names.$newItemId");
            if (!$newItemName) {
                continue; // Skip if the new item name is not provided
            }
            DetailMaintenanceInventoryReport::create([
                'header_id' => $header->id,
                'category_id' => 0, // or some identifier for new items
                'condition' => $request->input("new_conditions.$newItemId"),
                'remark' => $request->input("new_remarks.$newItemId"),
                'checked_by' => $request->input("checked_by.$newItemId"),
            ]);
        }

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
}
