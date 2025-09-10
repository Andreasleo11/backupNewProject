<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BusinessHomeController extends Controller
{
    public function index()
    {
        return view("business.home");
    }
}
