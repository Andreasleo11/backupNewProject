<?php

namespace App\Http\Controllers\hrd;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HrdHomeController extends Controller
{
    public function index()
    {
        return view('hrd.home');
    }
}
