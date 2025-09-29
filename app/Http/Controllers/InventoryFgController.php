<?php

namespace App\Http\Controllers;

use App\DataTables\sapInventoryFgDataTable;
use App\Models\sapInventoryFg;

class InventoryFgController extends Controller
{
    public function index(sapInventoryFgDataTable $dataTable)
    {
        // $datas = sapInventoryFg::paginate(10);

        return $dataTable->render('sap.inventoryfg');
    }
}
