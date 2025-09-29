<?php

namespace App\Http\Controllers\maintenance;

use App\Http\Controllers\Controller;

class MaintenanceHomeController extends Controller
{
    public function index()
    {
        return view('maintenance.home');
    }
}
