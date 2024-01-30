<?php

namespace App\Http\Controllers\director;

use App\Http\Controllers\Controller;
use App\Models\Report;

class DirectorHomeController extends Controller
{
    public function index()
    {
        $approvedDoc = Report::where('is_approve', 1)->count();
        $waitingDoc = Report::whereNull('is_approve')
            ->whereNotNull('autograph_1')
            ->whereNotNull('autograph_2')
            ->whereNotNull('autograph_3')->count();
        $rejectedDoc = Report::where('is_approve', 0)->count();

        return view('director.home', compact('approvedDoc', 'waitingDoc','rejectedDoc'));
    }
}
