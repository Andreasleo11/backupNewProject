<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LogisticHomeController extends Controller
{
    public function index(){
        return view('logistic.home');
    }
}
