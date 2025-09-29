<?php

namespace App\Http\Controllers;

class UserHomeController extends Controller
{
    public function index()
    {
        return view('user_home');
    }
}
