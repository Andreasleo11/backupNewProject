<?php

namespace App\Http\Controllers\production;

use App\Http\Controllers\Controller;

class ProductionHomeController extends Controller
{
    public function index()
    {
        return view('production');
    }
}
