<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductionHomeController extends Controller
{
    public function index(){
        return view('production.home');
    }
}
