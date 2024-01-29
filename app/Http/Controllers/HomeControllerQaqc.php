<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeControllerQaqc extends Controller
{
    public function index()
    {
        return view('qaqc.dashboard');
    }
}
