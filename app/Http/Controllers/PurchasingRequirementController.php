<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//panggil 2 datatable untuk show data 
// use App\DataTables\
// use App\DataTables\;

use App\DataTables\PurPorSummaryDataTable;
use App\DataTables\PurPorDetailDataTable;

class PurchasingRequirementController extends Controller
{
    public function index(PurPorSummaryDataTable $dataTable)
    {

        // return view("purchasing.requirement");
        return $dataTable->render("purchasing.requirement");
    }

    public function detail(PurPorDetailDataTable $dataTable)
    {
        // return view("purchasing.requirementdetail");
        return $dataTable->render("purchasing.requirementdetail");
    }
}
