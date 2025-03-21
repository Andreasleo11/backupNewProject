<?php

namespace App\Http\Controllers;

use App\Imports\PurchaseOrderImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseOrderImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $path = $request->file('file')->getRealPath();
        $data = Excel::import(new PurchaseOrderImport(auth()->user()->id), $request->file('file'), $path);

        return response()->json([
            'message' => 'Import success',
            'data' => $data,
        ]);
    }

    public function index()
    {
        return view('purchase_order_import.index');
    }
}
