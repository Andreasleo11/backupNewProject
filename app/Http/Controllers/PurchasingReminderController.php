<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\PurPorSummaryDataTable;
use App\DataTables\PurPorDetailDataTable;

class PurchasingReminderController extends Controller
{
    public function index(PurPorSummaryDataTable $dataTable)
    {
      
        return $dataTable->render("purchasing.reminder");
    }

    public function detail(PurPorDetailDataTable $dataTable)
    {
        // logic untuk retrieve detail data 

     
        return $dataTable->render("purchasing.reminderdetail");
    }
}
