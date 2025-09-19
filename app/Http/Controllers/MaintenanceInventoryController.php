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
    public function index(Request $request)
    {
        $periode = $request->input("periode");
        $year = $request->input("year", date("Y")); // Default to the current year if not provided

        $reportsQuery = HeaderMaintenanceInventoryReport::with("master");

        if ($periode) {
            switch ($periode) {
                case 1:
                    $reportsQuery
                        ->whereMonth("created_at", ">=", 1)
                        ->whereMonth("created_at", "<=", 4);
                    break;
                case 2:
                    $reportsQuery
                        ->whereMonth("created_at", ">=", 5)
                        ->whereMonth("created_at", "<=", 8);
                    break;
                case 3:
                    $reportsQuery
                        ->whereMonth("created_at", ">=", 9)
                        ->whereMonth("created_at", "<=", 12);
                    break;
            }
        }

        $reportsQuery->whereYear("created_at", $year);
        $headerData = $reportsQuery->get();
        $reports = $reportsQuery->orderBy("created_at", "desc")->paginate(10);

        // Collect master_ids from the header data
        $masterIdsInReports = $headerData->pluck("master_id")->unique();

        // Fetch all MasterInventory records
        $masterInventories = MasterInventory::all();

        // Prepare an array to store username: yes/no
        $usernameStatuses = [];

        foreach ($masterInventories as $masterInventory) {
            $usernameStatuses[$masterInventory->username] = $masterIdsInReports->contains(
                $masterInventory->id,
            )
                ? "yes"
                : "no";
        }
        // dd($usernameStatuses);

        return view(
            "maintenance-inventory.index",
            compact("reports", "periode", "year", "usernameStatuses"),
        );
    }

    private function getPeriodeCaturwulan($month)
    {
        if ($month >= 1 && $month <= 4) {
            return 1;
        } elseif ($month >= 5 && $month <= 8) {
            return 2;
        } else {
            return 3;
        }
    }

    public function create($id = null)
    {
        $currentMonth = now()->month;
        $periodeCaturwulan = $this->getPeriodeCaturwulan($currentMonth);

        // Check for the most recent header for the current period and matching master_id
        $existingHeader = HeaderMaintenanceInventoryReport::where(
            "periode_caturwulan",
            $periodeCaturwulan,
        )
            ->where("master_id", $id)
            ->latest("created_at") // Order by 'created_at' in descending order
            ->first(); // Get the latest record

        if ($existingHeader) {
            // Redirect to the show method if a header already exists
            return redirect()->route("maintenance.inventory.show", ["id" => $existingHeader->id]);
        }

        $masters = MasterInventory::all();
        $users = User::where(function ($query) {
            $query->where("name", "vicky")->orWhere("name", "bagus");
        })->get();
        $groups = GroupMaintenanceInventoryReport::with("detail")
            ->get()
            ->map(function ($group) {
                return [
                    "group_id" => $group->id, // Add group_id here
                    "group_name" => $group->name,
                    "details" => $group->detail
                        ->map(function ($detail) {
                            return [
                                "id" => $detail->id,
                                "name" => $detail->name,
                            ];
                        })
                        ->toArray(),
                ];
            })
            ->toArray();
        // dd($groups);

        return view("maintenance-inventory.create", compact("masters", "users", "groups", "id"));
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
            "no_dokumen" => HeaderMaintenanceInventoryReport::generateNoDokumen(),
            "master_id" => $request->input("master_id"),
            "revision_date" => $request->input("revision_date"),
        ]);
    }

    private function createDetails(StoreMaintenanceInventoryRequest $request, $headerId)
    {
        $items = $request->input("items", []);
        foreach ($items as $itemId) {
            DetailMaintenanceInventoryReport::create([
                "header_id" => $headerId,
                "category_id" => $itemId,
                "condition" => $request->input("conditions.$itemId"),
                "remark" => $request->input("remarks.$itemId"),
                "checked_by" => $request->input("checked_by.$itemId"),
            ]);
        }
    }

    private function createNewItems(StoreMaintenanceInventoryRequest $request, $headerId)
    {
        $newItems = $request->input("new_items", []);
        foreach ($newItems as $index => $newItemId) {
            $newItemName = $request->input("new_items_names.$newItemId");
            if (!$newItemName) {
                continue;
            }

            // Create a new CategoryMaintenanceInventoryReport
            $category = CategoryMaintenanceInventoryReport::create([
                "group_id" => $request->input("new_group_ids.$newItemId"), // Assuming you are passing group_id for new items
                "name" => $newItemName,
            ]);

            // Create a new DetailMaintenanceInventoryReport with the new category_id
            DetailMaintenanceInventoryReport::create([
                "header_id" => $headerId,
                "category_id" => $category->id,
                "condition" => $request->input("new_conditions.$newItemId"),
                "remark" => $request->input("new_remarks.$newItemId"),
                "checked_by" => $request->input("new_checked_by.$newItemId"),
            ]);
        }
    }

    private function handleRedirect(StoreMaintenanceInventoryRequest $request)
    {
        $action = $request->input("action");
        if ($action === "create_another") {
            return redirect()
                ->route("maintenance.inventory.create")
                ->with("success", "Maintenance Inventory Report created successfully!")
                ->withInput();
        } else {
            return redirect()
                ->route("maintenance.inventory.index")
                ->with("success", "Maintenance Inventory Report created successfully!");
        }
    }

    public function show($id)
    {
        $report = HeaderMaintenanceInventoryReport::with("detail", "detail.typecategory")->find(
            $id,
        );
        // dd($report);
        return view("maintenance-inventory.detail", compact("report"));
    }

    public function edit($id)
    {
        $masters = MasterInventory::all();
        $users = User::where(function ($query) {
            $query->where("name", "vicky")->orWhere("name", "bagus");
        })->get();
        $details = DetailMaintenanceInventoryReport::with("typecategory", "typecategory.group")
            ->where("header_id", $id)
            ->get();
        $groupedDetails = $details->groupBy(function ($item) {
            return $item->typecategory->group->name;
        });
        $report = HeaderMaintenanceInventoryReport::with("detail")->find($id);
        return view(
            "maintenance-inventory.edit",
            compact("report", "masters", "users", "groupedDetails"),
        );
    }

    public function update(UpdateMaintenanceInventoryRequest $request, $id)
    {
        // dd($request->all());
        $header = HeaderMaintenanceInventoryReport::findOrFail($id);

        $header->update([
            "master_id" => $request->input("master_id"),
            "revision_date" => $request->input("revision_date"),
        ]);

        $this->updateDetails($request, $header->id);

        // Handle new items
        if ($request->has("new_items")) {
            foreach ($request->input("new_items") as $newItemId) {
                DetailMaintenanceInventoryReport::create([
                    "header_id" => $header->id,
                    "name" => $request->input("new_items_names.$newItemId"),
                    "condition" => $request->input("new_conditions.$newItemId"),
                    "remark" => $request->input("new_remarks.$newItemId"),
                    "checked_by" => $request->input("new_checked_by.$newItemId"),
                    "group_id" => $request->input("new_group_ids.$newItemId"),
                ]);
            }
        }

        return redirect()
            ->back()
            ->with("success", "Maintenance Inventory Report successfully updated!");
    }

    private function updateDetails(UpdateMaintenanceInventoryRequest $request, $headerId)
    {
        $items = $request->input("items", []);
        foreach ($items as $itemId) {
            $detail = DetailMaintenanceInventoryReport::find($itemId);
            if ($detail) {
                $detail->update([
                    "condition" => $request->input("conditions.$itemId"),
                    "remark" => $request->input("remarks.$itemId"),
                    "checked_by" => $request->input("checked_by.$itemId"),
                ]);
            }
        }
    }
}
