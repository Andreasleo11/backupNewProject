<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PurchasingController extends Controller
{
    public function index()
    {
        return view('purchasing.purchasing_landing');
    }
    
}
