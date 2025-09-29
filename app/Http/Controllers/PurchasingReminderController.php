<?php

namespace App\Http\Controllers;

use App\DataTables\PurPorDetailDataTable;
use App\DataTables\PurPorSummaryDataTable;

class PurchasingReminderController extends Controller
{
    public function index(PurPorSummaryDataTable $dataTable)
    {
        return $dataTable->render('purchasing.reminder');
    }

    public function detail(PurPorDetailDataTable $dataTable)
    {
        // logic untuk retrieve detail data

        return $dataTable->render('purchasing.reminderdetail');
    }
}
