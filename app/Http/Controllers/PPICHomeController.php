<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PPICHomeController extends Controller
{
    public function index()
    {
        return view("ppic.home");
    }
}
