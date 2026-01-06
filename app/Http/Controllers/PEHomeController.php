<?php

namespace App\Http\Controllers;

class PEHomeController extends Controller
{
    public function index()
    {
        return view('PE.pe_landing');
    }
}
