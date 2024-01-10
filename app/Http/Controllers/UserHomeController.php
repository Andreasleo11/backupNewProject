<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserHomeController extends Controller
{
    public function index()
    {
        return view('user_home');
    }
}
