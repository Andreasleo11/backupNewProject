<?php

namespace App\Http\Controllers\hrd;

use App\Http\Controllers\Controller;
use App\Models\hrd\ImportantDoc;
use Carbon\Carbon;

class HrdHomeController extends Controller
{
    public function index()
    {
        // Calculate the expiry threshold (2 months from now)
        $expiryThreshold = Carbon::now()->addMonths(2);

        // Retrieve documents expiring within the next two months
        $importantDocs = ImportantDoc::where('expired_date', '<=', $expiryThreshold)->get();

        // Retrieve all documents
        $allImportantDocs = ImportantDoc::all();

        // Filter out documents expiring within the next two months
        $importantDocs2 = $allImportantDocs->diff($importantDocs);

        return view('hrd.home', compact('importantDocs', 'importantDocs2'));
    }
}
