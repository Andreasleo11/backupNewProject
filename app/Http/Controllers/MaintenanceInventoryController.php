<?php

namespace App\Http\Controllers;

use App\Models\HeaderMaintenanceInventoryReport;
use Illuminate\Http\Request;

class MaintenanceInventoryController extends Controller
{
    public function index()
    {
        $reports = HeaderMaintenanceInventoryReport::all();
        return view('maintenance-inventory.index', compact('reports'));
    }
}
