<?php

namespace App\Http\Controllers;

// panggil 2 datatable untuk show data
// use App\DataTables\
// use App\DataTables\;

use App\DataTables\PurPorDetailDataTable;
use App\DataTables\PurPorSummaryDataTable;

class PurchasingRequirementController extends Controller
{
    public function index(PurPorSummaryDataTable $dataTable)
    {
        // return view("purchasing.requirement");
        return $dataTable->render('purchasing.requirement');
    }

    public function detail(PurPorDetailDataTable $dataTable)
    {
        // return view("purchasing.requirementdetail");
        return $dataTable->render('purchasing.requirementdetail');
    }
}
