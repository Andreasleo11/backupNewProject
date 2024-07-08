<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MasterTintaController extends Controller
{
    public function index()
    {
        return view('stock-management.index');
    }

    public function transactiontintaview()
    {
        return view('stock-management.transaction');
    }

    public function storetransaction()
    {
        //function untuk store in / out stock tinta
    }
}
