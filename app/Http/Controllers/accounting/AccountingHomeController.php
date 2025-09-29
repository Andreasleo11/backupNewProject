<?php

namespace App\Http\Controllers\accounting;

use App\Http\Controllers\Controller;

class AccountingHomeController extends Controller
{
    public function index()
    {
        return view('accounting.home');
    }
}
