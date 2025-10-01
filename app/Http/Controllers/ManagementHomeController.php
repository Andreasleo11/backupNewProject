<?php

namespace App\Http\Controllers;

class ManagementHomeController extends Controller
{
    public function index()
    {
        return view('management.home');
    }
}
