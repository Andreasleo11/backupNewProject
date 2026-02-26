<?php

namespace App\Http\Controllers;

use App\Domain\Purchasing\SupplierEvaluation\Services\SupplierEvaluationService;
use App\Domain\Purchasing\SupplierEvaluation\Services\SupplierReportService;
use App\Models\PurchasingHeaderEvaluationSupplier;
use App\Models\PurchasingVendorAccuracyGood;
use App\Models\PurchasingVendorClaim;
use App\Models\PurchasingVendorClaimResponse;
use App\Models\PurchasingVendorListCertificate;
use App\Models\PurchasingVendorOntimeDelivery;
use App\Models\PurchasingVendorUrgentRequest;
use Illuminate\Http\Request;

class PurchasingSupplierEvaluationController extends Controller
{
    public function __construct(
        private readonly SupplierEvaluationService $evaluationService,
        private readonly SupplierReportService $reportService
    ) {}

    public function index()
    {
        $supplierData = $this->evaluationService->getSupplierData();
        $header = PurchasingHeaderEvaluationSupplier::get();
        
        return view('purchasing.evaluationsupplier.supplier_selection', compact('supplierData', 'header'));
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'supplier' => 'required|string',
            'start_month' => 'required|string',
            'start_year' => 'required|integer',
            'end_month' => 'required|string',
            'end_year' => 'required|integer',
        ]);

        $result = $this->evaluationService->createEvaluation($validated);

        if (! $result['success']) {
            $status = $result['message'] === 'Supplier not found' ? 404 : 400;

            return response()->json(['message' => $result['message']], $status);
        }

        return redirect()
            ->route('purchasing.evaluationsupplier.index')
            ->with('success', $result['message'] ?? 'Header and details updated successfully.');
    }

    public function details($id)
    {
        $data = $this->reportService->getDetailedView($id);

        return view('purchasing.evaluationsupplier.supplier_detail', $data);
    }

    // Kriteria views - simple data retrieval
    public function kriteria1(Request $request)
    {
        $query = PurchasingVendorClaim::query();

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('month')) {
            $query->whereMonth('incoming_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('incoming_date', $request->year);
        }

        $datas = $query->orderBy('claim_start_date', 'asc')
            ->get()
            ->map(function ($data) {
                $data->incoming_date = \Carbon\Carbon::parse($data->incoming_date)->format('d-m-Y');
                $data->claim_start_date = \Carbon\Carbon::parse($data->claim_start_date)->format('d-m-Y');
                $data->claim_finish_date = \Carbon\Carbon::parse($data->claim_finish_date)->format('d-m-Y');

                return $data;
            });

        $vendorNames = PurchasingVendorClaim::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria1', compact('datas', 'vendorNames'));
    }

    public function kriteria2(Request $request)
    {
        $query = PurchasingVendorAccuracyGood::query();

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('month')) {
            $query->whereMonth('incoming_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('incoming_date', $request->year);
        }

        $datas = $query->orderBy('incoming_date', 'asc')->get();
        $vendorNames = PurchasingVendorAccuracyGood::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria2', compact('datas', 'vendorNames'));
    }

    public function kriteria3(Request $request)
    {
        $query = PurchasingVendorOntimeDelivery::query();

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('month')) {
            $query->whereMonth('request_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('request_date', $request->year);
        }

        $datas = $query->get();
        $vendorNames = PurchasingVendorOntimeDelivery::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria3', compact('datas', 'vendorNames'));
    }

    public function kriteria4(Request $request)
    {
        $query = PurchasingVendorUrgentRequest::query();

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('month')) {
            $query->whereMonth('incoming_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('incoming_date', $request->year);
        }

        $datas = $query->orderBy('incoming_date', 'asc')->get();
        $vendorNames = PurchasingVendorUrgentRequest::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria4', compact('datas', 'vendorNames'));
    }

    public function kriteria5(Request $request)
    {
        $query = PurchasingVendorClaimResponse::query();

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        if ($request->filled('month')) {
            $query->whereMonth('cpar_response_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('cpar_response_date', $request->year);
        }

        $datas = $query->get();
        $vendorNames = PurchasingVendorClaimResponse::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria5', compact('datas', 'vendorNames'));
    }

    public function kriteria6(Request $request)
    {
        $query = PurchasingVendorListCertificate::query();

        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'like', '%' . $request->vendor_name . '%');
        }

        $datas = $query->get();
        $vendorNames = PurchasingVendorListCertificate::distinct('vendor_name')->pluck('vendor_name');

        return view('purchasing.evaluationsupplier.kriteria6', compact('datas', 'vendorNames'));
    }
}
