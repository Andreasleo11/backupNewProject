<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PersonaliaHomeController extends Controller
{
    public function index(){
        return view('personalia.home');
    }
}
