<?php

namespace App\Http\Controllers;

use App\DataTables\AccountingPurchaseRequestDataTable;

class AccountingPurchaseRequestController extends Controller
{
    public function index(AccountingPurchaseRequestDataTable $dataTable)
    {
        return $dataTable->render('accounting.purchase-requests');
    }
}
