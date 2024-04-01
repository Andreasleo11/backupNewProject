<?php

namespace App\Http\Controllers;

use App\Models\sapInventoryFg;


use Illuminate\Http\Request;
use App\DataTables\sapInventoryFgDataTable;



class InventoryFgController extends Controller
{
    public function index(sapInventoryFgDataTable $dataTable)
    {
        // $datas = sapInventoryFg::paginate(10);


        return $dataTable->render("sap.inventoryfg");
    }
}
