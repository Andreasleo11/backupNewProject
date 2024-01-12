<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffHomeController extends Controller
{
    public function index()
    {
        return view('staff_home');
    }

    public function indexhome()
    {
        return view('user_home');
    }
}
