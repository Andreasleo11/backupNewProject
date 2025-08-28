<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\MasterStock;
use App\Models\StockTransaction;
use App\Models\StockType;
use App\Models\StockRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class MasterTintaController extends Controller
{
    public function index()
    {
        $datas = MasterStock::with("stocktype")->get();
        return view("stock-management.index", compact("datas"));
    }

    public function transactiontintaview()
    {
        $departments = Department::all();
        $masterStocks = MasterStock::with("stocktype")->get();
        $stockRequest = StockRequest::get();
        return view(
            "stock-management.transaction",
            compact("departments", "masterStocks", "stockRequest"),
        );
    }

    public function storetransaction(Request $request)
    {
        //function untuk store in / out stock tinta
        $datas = $request->all();
        // dd($datas);
        // Extract the stock ID
        $stockId = $datas["stock_id"];

        $transactiontype = $datas["transaction_type"];

        // Filter and organize the item names
        $itemNames = [];
        foreach ($datas as $key => $value) {
            if (strpos($key, "item_name_") === 0) {
                $itemNames[] = $value;
            }
        }

        if ($transactiontype === "out") {
            $department = $datas["department"];
            $pic = $datas["pic"];
            $remark = $datas["remark"];
            foreach ($itemNames as $itemName) {
                StockTransaction::where("unique_code", $itemName)
                    ->where("stock_id", $stockId)
                    ->update([
                        "dept_id" => $department,
                        "out_time" => now(),
                        "is_out" => true,
                        "receiver" => $pic,
                        "remark" => $remark,
                    ]);

                MasterStock::where("id", $stockId)->decrement("stock_quantity", 1);
                StockRequest::where("stock_id", $stockId)
                    ->where("dept_id", $department)
                    ->whereYear("created_at", Carbon::now()->year)
                    ->whereMonth("created_at", Carbon::now()->month)
                    ->latest()
                    ->decrement("quantity_available", 1);
            }
        } else {
            foreach ($itemNames as $itemName) {
                StockTransaction::create([
                    "stock_id" => $stockId,
                    "in_time" => now(),
                    "unique_code" => $itemName, // Assuming unique_code is the same as item_name
                    // Add other necessary fields here
                ]);
            }

            $itemsCount = count($itemNames);

            MasterStock::where("id", $stockId)->increment("stock_quantity", $itemsCount);
        }

        return redirect()
            ->route("mastertinta.index")
            ->with("success", "Stock request created successfully!");
    }

    public function getItems($masterStockId)
    {
        $items = StockTransaction::with("historyTransaction")
            ->where("stock_id", $masterStockId)
            ->where("is_out", false)
            ->get();
        Log::info("items : $items");
        return response()->json($items);
    }

    public function requestpageindex(Request $request)
    {
        // Fetch all master stocks and departments for dropdown options
        $masterStocks = MasterStock::all();
        $departments = Department::all();

        // Start building the query for StockRequest
        $query = StockRequest::with(
            "stockRelation",
            "stockRelation.stockType",
            "deptRelation",
        )->orderBy("month", "desc");

        // Filter by stock_id if provided
        if ($request->filled("stock_id")) {
            $query->where("stock_id", $request->stock_id);
        }

        // Filter by dept_id if provided
        if ($request->filled("dept_id")) {
            $query->where("dept_id", $request->dept_id);
        }

        // Filter by month if provided
        if ($request->filled("month")) {
            $month = date("m", strtotime($request->month));
            $query
                ->whereMonth("month", $month)
                ->whereYear("month", date("Y", strtotime($request->month)));
        }

        // Retrieve filtered data
        $datas = $query->get();

        // Return view with filtered data and dropdown options
        return view("stock-management.requestindex", [
            "datas" => $datas,
            "masterStocks" => $masterStocks,
            "departments" => $departments,
        ]);
    }

    public function requeststore(Request $request)
    {
        $datas = $request->all();

        $validatedData = $request->validate([
            "masterStock" => "required|integer",
            "department" => "required|integer",
            "stockRequest" => "required|integer",
            "month" => "required|date",
            "remark" => "nullable|string",
        ]);

        $month = date("m", strtotime($validatedData["month"]));

        $existingStockRequest = StockRequest::where("stock_id", $validatedData["masterStock"])
            ->where("dept_id", $validatedData["department"])
            ->latest()
            ->first();

        $masterStock = MasterStock::find($validatedData["masterStock"]);

        if (!$masterStock) {
            return redirect()
                ->back()
                ->withErrors(["masterStock" => "Invalid MasterStock ID."]);
        }

        $sumRequested = StockRequest::where("stock_id", $validatedData["masterStock"])
            ->whereMonth("month", $month)
            ->sum("quantity_available");

        if ($masterStock->stock_quantity > $validatedData["stockRequest"] + $sumRequested) {
            // dd('masuk if');
            $quantityAvailable = $validatedData["stockRequest"];
        } else {
            // dd('masukelse');
            $quantityAvailable = $masterStock->stock_quantity - $sumRequested;
            if ($quantityAvailable < 0) {
                $quantityAvailable = 0;
            }
        }

        if ($existingStockRequest) {
            $quantityAvailable += $existingStockRequest->quantity_available;
        }

        $stockRequest = new StockRequest();
        $stockRequest->stock_id = $validatedData["masterStock"];
        $stockRequest->dept_id = $validatedData["department"];
        $stockRequest->request_quantity = $validatedData["stockRequest"];
        $stockRequest->month = $validatedData["month"];
        $stockRequest->remark = $validatedData["remark"];
        $stockRequest->quantity_available = $quantityAvailable;

        $stockRequest->save();

        return redirect()
            ->route("testing.request")
            ->with("success", "Stock request created successfully!");
    }

    public function getAvailableQuantity($stock_id, $department_id)
    {
        $latestTransaction = StockRequest::where("stock_id", $stock_id)
            ->where("dept_id", $department_id)
            ->orderBy("month", "desc")
            ->latest()
            ->first();

        return response()->json([
            "available_quantity" => $latestTransaction->quantity_available
                ? $latestTransaction->quantity_available
                : 0,
        ]);
    }

    public function listtransaction(Request $request)
    {
        // Fetch all master stocks for dropdown options
        $masterStocks = MasterStock::all();

        // Start building the query for StockTransaction
        $query = StockTransaction::with("historyTransaction", "deptRelation");

        // Filter by stock_id if provided
        if ($request->filled("stock_id")) {
            $query->where("stock_id", $request->stock_id);
        }

        // Retrieve filtered data
        $datas = $query->get();

        // Return view with filtered data and dropdown options
        return view("stock-management.listtransaction", [
            "datas" => $datas,
            "masterStocks" => $masterStocks,
        ]);
    }
}
