<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\MasterStock;
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
        return view('stock-management.transaction', compact('types', 'departments'));
    }

    public function storetransaction(Request $request)
    {
        //function untuk store in / out stock tinta 
        dd($request->all());
        $datas = $request->all();

    }
}
