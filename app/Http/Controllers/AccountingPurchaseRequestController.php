<?php

namespace App\Http\Controllers;

use App\DataTables\AccountingPurchaseRequestDataTable;
use Illuminate\Http\Request;

class AccountingPurchaseRequestController extends Controller
{
    public function index(AccountingPurchaseRequestDataTable $dataTable)
    {
        return $dataTable->render("accounting.purchase-requests");
    }
}
