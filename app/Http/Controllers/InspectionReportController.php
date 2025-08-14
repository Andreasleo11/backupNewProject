<?php

namespace App\Http\Controllers;

use App\Models\InspectionForm\InspectionReport;
use Illuminate\Http\Request;

class InspectionReportController extends Controller
{
    public function index(Request $request)
    {
        // optional quick search by document number or customer
        $search = $request->query('s');

        $reports = InspectionReport::when($search, function ($q) use ($search) {
            $q->where('document_number', 'like', "%{$search}%")
                ->orWhere('customer', 'like', "%{$search}%");
        })
            ->latest('inspection_date')   // order newest first
            ->paginate(10)
            ->withQueryString();           // keep ?s=…
        return view('inspection.index', compact('reports', 'search'));
    }

    public function create()
    {
        return view('inspection.create');
    }

    public function show(InspectionReport $inspectionReport)
    {
        /** 
         * Eager-load whatever relations you need.
         * Adjust the with() list to your actual relations.
         */
        $inspectionReport->load([
            'detailInspectionReports',
            'detailInspectionReports.firstInspections',
            'dimensionData',
            'detailInspectionReports.secondInspections',
            'detailInspectionReports.secondInspections.samplingData',
            'detailInspectionReports.secondInspections.packagingData',
            'detailInspectionReports.judgementData',
            'quantityData',
            'problemData',
        ]);

        return view('inspection.show', compact('inspectionReport'));
    }
}
