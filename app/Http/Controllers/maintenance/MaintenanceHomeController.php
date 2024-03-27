<?php

namespace App\Http\Controllers\maintenance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaintenanceHomeController extends Controller
{
    public function index(){
        return view('maintenance.home');
    }
}
