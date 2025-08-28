<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StoreHomeController extends Controller
{
    public function index()
    {
        return view("store.home");
    }
}
