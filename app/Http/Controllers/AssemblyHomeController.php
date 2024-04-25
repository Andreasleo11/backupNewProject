<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AssemblyHomeController extends Controller
{
    public function index(){
        return view('assembly.home');
    }
}
