<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PIHomeController extends Controller
{
    public function index()
    {
        return view("pi.home");
    }
}
