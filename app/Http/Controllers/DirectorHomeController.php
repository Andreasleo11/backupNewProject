<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DirectorHomeController extends Controller
{
    public function index()
    {
        return view('direktur.dashboard');
    }
}
