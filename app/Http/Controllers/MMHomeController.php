<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MMHomeController extends Controller
{
    public function index()
    {
        return view("MM.home");
    }
}
