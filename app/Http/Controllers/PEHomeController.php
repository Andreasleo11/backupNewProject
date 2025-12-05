<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PEHomeController extends Controller
{
    public function index(){
        return view('PE.pe_landing');
    }
}
