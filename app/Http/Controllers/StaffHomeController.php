<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffHomeController extends Controller
{
    public function index()
    {
        $approvedDoc = Report::where('is_approve', 1)->count();
        $waitingDoc = Report::whereNull('is_approve')->count();
        $rejectedDoc = Report::where('is_approve', 0)->count();

        return view('staff_home', compact('approvedDoc', 'waitingDoc','rejectedDoc'));
    }

}
