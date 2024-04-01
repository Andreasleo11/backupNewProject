<?php

namespace App\Http\Controllers\director;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\Report;

class DirectorHomeController extends Controller
{
    public function index()
    {
        $reportCounts = [
            'approved' => Report::approved()->count(),
            'waiting' => Report::waitingApproval()->count(),
            'rejected' => Report::rejected()->count(),
        ];

        $purchaseRequestCounts = [
            'approved' => PurchaseRequest::approved()->count(),
            'waiting' => PurchaseRequest::waiting()->count(),
            'rejected' => PurchaseRequest::rejected()->count(),
        ];

        return view('director.home', compact('reportCounts', 'purchaseRequestCounts'));
    }
}
