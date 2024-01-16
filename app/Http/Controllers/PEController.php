<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PEController extends Controller
{
    public function index()
    {
        return view('PE.pe_landing');
    }
}
