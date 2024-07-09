<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\MasterStock;
use App\Models\StockTransaction;
use App\Models\StockType;

class MasterTintaController extends Controller
{
    public function index()
    {
        $datas = MasterStock::with('stocktype')->get();
        return view('stock-management.index', compact('datas'));
    }

    public function transactiontintaview()
    {
        $types = StockType::all();
        $departments = Department::all();
        $datas = MasterStock::with('stocktype')->get();
        return view('stock-management.transaction', compact('types', 'departments', 'datas'));
    }

    public function storetransaction(Request $request)
    {
        //function untuk store in / out stock tinta 
        $datas = $request->all();
    
        // Extract the stock ID
        $stockId = $datas['stock_id'];
    
        // Filter and organize the item names
        $itemNames = [];
        foreach ($datas as $key => $value) {
            if (strpos($key, 'item_name_') === 0) {
                $itemNames[] = $value;
            }
        }

        foreach ($itemNames as $itemName) {
            StockTransaction::create([
                'stock_id' => $stockId,
                'in_time' =>  now(),
                'unique_code' => $itemName, // Assuming unique_code is the same as item_name
                // Add other necessary fields here
            ]);
        }

        $itemsCount = count($itemNames);

        MasterStock::where('id', $stockId)->increment('stock_quantity', $itemsCount);

        return response()->json(['message' => 'Stock transactions stored successfully']);
        
    }
}
