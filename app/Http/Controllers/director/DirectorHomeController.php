<?php

namespace App\Http\Controllers\director;

use App\Http\Controllers\Controller;
use App\Models\Report;

class DirectorHomeController extends Controller
{
    public function index()
    {
        $approvedDoc = Report::withAutographs()->where('is_approve', 1)->count();
        $waitingDoc = Report::withAutographs()->whereNull('is_approve')->count();
        $rejectedDoc = Report::withAutographs()->where('is_approve', 0)->count();

        return view('director.home', compact('approvedDoc', 'waitingDoc','rejectedDoc'));
    }
}
