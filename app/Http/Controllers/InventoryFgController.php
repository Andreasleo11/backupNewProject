<?php

namespace App\Http\Controllers;

use App\Models\sapInventoryFg;


use Illuminate\Http\Request;


class InventoryFgController extends Controller
{
    public function index()
    {
        $datas = sapInventoryFg::paginate(10);


        return view("sap.inventoryfg", compact("datas"));
    }
}
