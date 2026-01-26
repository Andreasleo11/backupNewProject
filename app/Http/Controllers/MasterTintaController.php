<?php

namespace App\Http\Controllers;

use App\Domain\MasterData\Services\StockManagementService;
use App\Models\Department;
use App\Models\MasterStock;
use Illuminate\Http\Request;

class MasterTintaController extends Controller
{
    public function __construct(
        private readonly StockManagementService $stockService
    ) {}

    public function index()
    {
        $datas = MasterStock::with('stocktype')->get();

        return view('stock-management.index', compact('datas'));
    }

    public function transactiontintaview()
    {
        $departments = Department::all();
        $masterStocks = MasterStock::with('stocktype')->get();
        $stockRequest = \App\Models\StockRequest::all();

        return view('stock-management.transaction', compact('departments', 'masterStocks', 'stockRequest'));
    }

    public function storetransaction(Request $request)
    {
        $this->stockService->storeTransaction($request->all());

        return redirect()->route('mastertinta.index')->with('success', 'Stock request created successfully!');
    }

    public function getItems($masterStockId)
    {
        $items = $this->stockService->getAvailableItems($masterStockId);

        return response()->json($items);
    }

    public function requestpageindex(Request $request)
    {
        $masterStocks = MasterStock::all();
        $departments = Department::all();

        $filters = $request->only(['stock_id', 'dept_id', 'month']);
        $datas = $this->stockService->getFilteredStockRequests($filters);

        return view('stock-management.requestindex', compact('datas', 'masterStocks', 'departments'));
    }

    public function requeststore(Request $request)
    {
        $request->validate([
            'masterStock' => 'required|integer',
            'department' => 'required|integer',
            'stockRequest' => 'required|integer',
            'month' => 'required|date',
            'remark' => 'nullable|string',
        ]);

        $this->stockService->createStockRequest($request->all());

        return redirect()->route('testing.request')->with('success', 'Stock request created successfully!');
    }

    public function getAvailableQuantity($stock_id, $department_id)
    {
        $availableQuantity = $this->stockService->getAvailableQuantity($stock_id, $department_id);

        return response()->json(['available_quantity' => $availableQuantity]);
    }

    public function listtransaction(Request $request)
    {
        $masterStocks = MasterStock::all();
        $datas = $this->stockService->getFilteredTransactions($request->filled('stock_id') ? $request->stock_id : null);

        return view('stock-management.listtransaction', compact('datas', 'masterStocks'));
    }
}
