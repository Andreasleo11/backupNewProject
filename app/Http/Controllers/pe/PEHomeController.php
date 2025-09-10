<?php

namespace App\Http\Controllers\pe;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PEHomeController extends Controller
{
    public function index()
    {
        return view("PE.pe_landing");
    }
}
