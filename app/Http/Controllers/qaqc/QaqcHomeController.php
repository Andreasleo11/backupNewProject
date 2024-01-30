<?php

namespace App\Http\Controllers\qaqc;

use App\Http\Controllers\Controller;
use App\Models\Report;

class QaqcHomeController extends Controller
{
    public function index()
    {

        $approvedDoc = Report::where('is_approve', 1)->count();
        $waitingDoc = Report::whereNull('is_approve')->count();
        $rejectedDoc = Report::where('is_approve', 0)->count();

        return view('qaqc.home', compact('approvedDoc', 'waitingDoc','rejectedDoc'));
    }
}
