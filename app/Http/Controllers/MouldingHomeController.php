<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MouldingHomeController extends Controller
{
    public function index(){
        return view('moulding.home');
    }
}
