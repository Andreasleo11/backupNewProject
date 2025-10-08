<?php

namespace App\Http\Controllers;

use App\DataTables\sapInventoryMtrDataTable;
use App\Models\sapInventoryMtr;

class InventoryMtrController extends Controller
{
    public function index(sapInventoryMtrDataTable $dataTable)
    {
        // $datas = sapInventoryMtr::paginate(10);

        return $dataTable->render('sap.inventorymtr');
    }
}
