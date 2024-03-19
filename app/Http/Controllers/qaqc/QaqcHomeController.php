<?php

namespace App\Http\Controllers\qaqc;

use App\Http\Controllers\Controller;
use App\Models\Report;

class QaqcHomeController extends Controller
{
    public function index()
    {

        $approvedDoc = Report::where('is_approve', 1)->count();
        $waitingSignatureDoc = Report::withAutographs()->whereNull('is_approve')->count();
        $waitingDoc = Report::whereNull('is_approve')->count() - $waitingSignatureDoc;
        $rejectedDoc = Report::where('is_approve', 0)->count();

        return view('qaqc.home', compact('approvedDoc', 'waitingSignatureDoc', 'waitingDoc','rejectedDoc'));
    }
}
