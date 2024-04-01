<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ComputerHomeController extends Controller
{
    public function index()
    {
        return view('computer.home');
    }
}
