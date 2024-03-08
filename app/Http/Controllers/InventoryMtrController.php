<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sapInventoryMtr;

class InventoryMtrController extends Controller
{
    public function index()
    {
        $datas = sapInventoryMtr::paginate(10);


        return view("sap.inventorymtr", compact("datas"));
    }
}
