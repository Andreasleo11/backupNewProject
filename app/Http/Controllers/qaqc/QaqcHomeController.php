<?php

namespace App\Http\Controllers\qaqc;

use App\Http\Controllers\Controller;
use App\Models\Report;

class QaqcHomeController extends Controller
{
    public function index()
    {
        $approvedDoc = Report::approved()->count();
        $waitingSignatureDoc = Report::waitingSignature()->count();
        $waitingApprovalDoc = Report::waitingApproval()->count();
        $rejectedDoc = Report::rejected()->count();

        return view(
            "qaqc.home",
            compact("approvedDoc", "waitingSignatureDoc", "waitingApprovalDoc", "rejectedDoc"),
        );
    }
}
