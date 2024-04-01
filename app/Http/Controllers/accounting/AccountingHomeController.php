<?php

namespace App\Http\Controllers\accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountingHomeController extends Controller
{
    public function index(){
        return view('accounting.home');
    }
}
