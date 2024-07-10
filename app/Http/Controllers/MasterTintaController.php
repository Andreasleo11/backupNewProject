<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\MasterStock;
use App\Models\StockTransaction;
use App\Models\StockType;
use Illuminate\Support\Facades\Log;

class MasterTintaController extends Controller
{
    public function index()
    {
        $datas = MasterStock::with('stocktype')->get();
        return view('stock-management.index', compact('datas'));
    }

    public function transactiontintaview()
    {
        $departments = Department::all();
        $masterStocks = MasterStock::with('stocktype')->get();
        return view('stock-management.transaction', compact('departments', 'masterStocks'));
    }

    public function storetransaction(Request $request)
    {
        //function untuk store in / out stock tinta
        $datas = $request->all();
        // dd($datas);
        // Extract the stock ID
        $stockId = $datas['stock_id'];

        $transactiontype = $datas['transaction_type'];

        $department = $datas['department'];
        $pic = $datas['pic'];
        $remark = $datas['remark'];
    
        // Filter and organize the item names
        $itemNames = [];
        foreach ($datas as $key => $value) {
            if (strpos($key, 'item_name_') === 0) {
                $itemNames[] = $value;
            }
        }

        if($transactiontype ===  'out')
        {
            foreach ($itemNames as $itemName) {
                StockTransaction::where('unique_code', $itemName)
                    ->update([
                        'dept_id' => $department,
                        'out_time' => now(),
                        'is_out' => true,
                        'receiver' => $pic,
                        'remark' => $remark,
                    ]);

                    MasterStock::where('id', $stockId)->decrement('stock_quantity', 1);
            }
        }
        else{
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
        }

        return response()->json(['message' => 'Stock transactions stored successfully']);
    }

    public function getItems($masterStockId)
    {
        $items = StockTransaction::with('historyTransaction')
            ->where('stock_id', $masterStockId)
            ->where('is_out', false)
            ->get();
        Log::info("items : $items");
        return response()->json($items);
    }

    public function requestpageindex()
    {
        return view('stock-management.requestindex');
    }
}
