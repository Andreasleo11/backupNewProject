<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\sapInventoryMtr;

use App\DataTables\sapInventoryMtrDataTable;

class InventoryMtrController extends Controller
{
    public function index(sapInventoryMtrDataTable $dataTable)
    {
        // $datas = sapInventoryMtr::paginate(10);


        
        return $dataTable->render("sap.inventorymtr");
    }
}
