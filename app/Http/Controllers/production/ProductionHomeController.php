<?php

namespace App\Http\Controllers\production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductionHomeController extends Controller
{
    public function index(){
        return view('production.home');
    }
}
