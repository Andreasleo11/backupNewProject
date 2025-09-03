<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MUHomeController extends Controller
{
    public function index()
    {
        return view("MU.home");
    }
}
