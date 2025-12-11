<?php

namespace App\Http\Controllers;

use App\DataTables\PurchaseOrderDataTable;
use App\Exports\PurchaseOrderExport;
use App\Http\Requests\StorePoRequest;
use App\Http\Requests\UpdatePoRequest;
use App\Jobs\POSignPDFJob;
use App\Models\File;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderCategory;
use App\Models\PurchaseOrderDownloadLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\Fpdi;

class PurchaseOrderController extends Controller
{
    public function index(PurchaseOrderDataTable $dataTable, Request $request)
    {
        $month = $request->query('month');

        return $dataTable->with(['month' => $month])->render('purchase_order.index');
    }

    public function approveSelected(Request $request)
    {
        if (auth()->user()->specification->name === 'DIRECTOR') {
            $ids = $request->input('ids');
            $purchaseOrders = PurchaseOrder::whereIn('id', $ids)->get();

            foreach ($purchaseOrders as $po) {
                POSignPDFJob::dispatch($po);
            }

            return response()->json(['message' => 'Selected purchase orders approved.']);
        }

        return response()->json(['message' => 'No permission granted.']);
    }

    public function rejectSelected(Request $request)
    {
        if (auth()->user()->specification->name === 'DIRECTOR') {
            $ids = $request->input('ids');
            $reason = $request->input('reason');

            if (! $ids || ! $reason) {
                return response()->json(['message' => 'Invalid request.'], 400);
            }

            PurchaseOrder::whereIn('id', $ids)->update([
                'status' => 3,
                'reason' => $reason,
            ]);

            return response()->json(['message' => 'Selected purchase orders rejected.']);
        }

        return response()->json(['message' => 'No permission granted.']);
    }

    public function create(Request $request)
    {
        $categories = PurchaseOrderCategory::all();
        $parentPONumber = $request->get('parent_po_number', null);

        return view('purchase_order.create', compact('categories', 'parentPONumber'));
    }

    public function store(StorePoRequest $request)
    {
        // Process validated data
        $validated = $request->validated();
        // dd($validated);
        // Convert invoice_date from 'dd.mm.yy' to 'yyyy-mm-dd'
        if (isset($validated['invoice_date'])) {
            $date = \DateTime::createFromFormat('d.m.y', $validated['invoice_date']);
            if ($date) {
                $validated['invoice_date'] = $date->format('Y-m-d');
            } else {
                return redirect()
                    ->back()
                    ->withInputs(['invoice_date' => 'Invalid date format']);
            }
        }

        // Store the uploaded PDF with a unique filename
        $file = $validated['pdf_file'];
        $filename = 'PO_'.$validated['po_number'].'_'.time().'.pdf';
        $file->storeAs('public/pdfs', $filename);

        // Remove commas from the total and convert it to a float
        $total = (float) str_replace(',', '', $validated['total']);

        // Create a new PurchaseOrder record using Eloquent
        $purchaseOrder = new PurchaseOrder;
        $purchaseOrder->po_number = $validated['po_number'];
        $purchaseOrder->status = 1;
        $purchaseOrder->filename = $filename;
        $purchaseOrder->creator_id = auth()->id();
        $purchaseOrder->vendor_name = $validated['vendor_name'];
        $purchaseOrder->invoice_date = $validated['invoice_date'];
        $purchaseOrder->invoice_number = $validated['invoice_number'];
        $purchaseOrder->currency = $validated['currency'];
        $purchaseOrder->total = $total;
        $purchaseOrder->purchase_order_category_id = $validated['purchase_order_category_id'];
        $purchaseOrder->tanggal_pembayaran = $validated['tanggal_pembayaran'];

        if (! empty($validated['parent_po_number'])) {
            $purchaseOrder->parent_po_number = $validated['parent_po_number'];

            // Update the canceled PO revision_count
            $parentPO = PurchaseOrder::where('po_number', $validated['parent_po_number'])->first();
            if ($parentPO) {
                $parentPO->update([
                    'revision_count' => $parentPO->revision_count + 1,
                ]);
            }
        }

        $purchaseOrder->save();

        return redirect()->route('po.index')->with('success', 'PO created successfully.');
    }

    public function view($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);

        $revisions = PurchaseOrder::where('parent_po_number', $purchaseOrder->po_number)->get();

        $user = Auth::user();
        $files = File::where('doc_id', $purchaseOrder->po_number)->get();
        $director = $user->specification->name == 'DIRECTOR';

        $filename = $purchaseOrder->filename;
        // Check if the PDF exists in storage
        if (! Storage::exists('public/pdfs/'.$purchaseOrder->filename)) {
            // abort(500, 'PDF file not found.');
        }

        return view(
            'purchase_order.view',
            compact('purchaseOrder', 'user', 'files', 'revisions', 'director'),
        );
    }

    public function sign(Request $request)
    {
        // version click langsung keluar tanda tangan
        $id = $request->input('id');
        $filename = $request->input('filename');
        $signedPdfPath = $this->signPDF($id, $filename);

        $PurchaseOrder = PurchaseOrder::find($id);
        if ($PurchaseOrder) {
            $PurchaseOrder->filename = basename($signedPdfPath); // Save only the file name, not the full path
            $PurchaseOrder->approved_date = now();
            $PurchaseOrder->status = 2;
            $PurchaseOrder->save();
        }

        return response()->json(['message' => 'PDF signed successfully!']);
    }

    private function signPDF($id, $filename)
    {
        $pdfPath = public_path("storage/pdfs/{$filename}");
        $signedPdfPath = str_replace('.pdf', '_signed.pdf', $pdfPath);

        // Initialize FPDI
        $pdf = new Fpdi;
        $pageCount = $pdf->setSourceFile($pdfPath);

        // Path to the stored signature file
        $signaturePath = public_path('autographs/Djoni.png');

        // Loop through each page and add it to the new PDF
        for ($pageIndex = 1; $pageIndex <= $pageCount; $pageIndex++) {
            $pdf->AddPage();
            $templateId = $pdf->importPage($pageIndex);
            $pdf->useTemplate($templateId, 0, 0, 210);

            // Check if this is the last page
            if ($pageIndex === $pageCount) {
                $pdf->SetFont('Arial', '', 12); // Set font before adding text if necessary
                $pdf->Image($signaturePath, 40, 250, 40, 20); // Position the signature on the last page
            }
        }

        // Save the signed PDF
        $pdf->Output($signedPdfPath, 'F');

        return $signedPdfPath;
    }

    public function rejectPDF(Request $request)
    {
        $data = PurchaseOrder::find($request->input('id'));

        if (! $data) {
            return response()->json(['message' => 'PO not found.'], 404);
        }

        $data->reason = $request->input('reason');
        $data->status = 3; // Optionally, set a different status to indicate "Rejected"
        $data->save();

        return response()->json(['message' => 'PO rejected successfully.']);
    }

    public function downloadPDF($id)
    {
        try {
            // Attempt to find the PurchaseOrder record
            $po = PurchaseOrder::findOrFail($id);

            $filename = $po->filename;
            $path = storage_path("app/public/pdfs/{$filename}");

            // Check if the file exists in the specified path
            if (! file_exists($path)) {
                abort(404, 'PDF file not found.');
            }

            // Update the 'downloaded_at' timestamp for the PO only for creator_id
            if ($po->creator_id === auth()->user()->id) {
                // $po->update(['downloaded_at' => now()]);
                PurchaseOrderDownloadLog::create([
                    'user_id' => auth()->user()->id,
                    'purchase_order_id' => $po->id,
                ]);
            }

            // Return the response to download the PDF file
            return response()->download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the PurchaseOrder record is not found
            return response()->json(['error' => 'Purchase order not found.'], 404);
        } catch (\Exception $e) {
            // Return the exception message for debugging purposes
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $po = PurchaseOrder::find($id);

            if (! $po) {
                return redirect()->route('po.index')->with('error', 'PO not found!');
            }

            $po->delete();

            return redirect()->route('po.index')->with('success', 'PO deleted successfully!');
        } catch (\Exception $e) {
            // Log the exception message for debugging
            Log::error("Error deleting PO with ID {$id}: ".$e->getMessage());

            return redirect()
                ->route('po.index')
                ->with(
                    'error',
                    'An error occurred while trying to delete the PO. Please try again later.',
                );
        }
    }

    public function rejectAll(Request $request)
    {
        $ids = $request->input('ids');
        $reason = $request->input('reason', 'No reason provided');

        // Fetch all requested PO records and separate by 'APPROVED' and 'REJECTED' statuses
        $approvedPOs = PurchaseOrder::whereIn('id', $ids)->where('status', 2)->pluck('po_number');
        $rejectedPOs = PurchaseOrder::whereIn('id', $ids)->where('status', 3)->pluck('po_number');

        // If any approved or rejected POs are found, return an error
        if ($approvedPOs->isNotEmpty() || $rejectedPOs->isNotEmpty()) {
            $message = 'Cannot reject selected POs. ';

            if ($approvedPOs->isNotEmpty()) {
                $message .=
                    'The following PO Numbers are already approved: '.
                    $approvedPOs->join(', ').
                    '. ';
            }

            if ($rejectedPOs->isNotEmpty()) {
                $message .=
                    'The following PO Numbers are already rejected: '.
                    $rejectedPOs->join(', ').
                    '.';
            }

            return response()->json(['message' => $message], 400);
        }

        // Proceed to reject POs if no conflicts
        PurchaseOrder::whereIn('id', $ids)->update([
            'status' => 3,
            'reason' => $reason,
        ]);

        return response()->json(['message' => 'All selected POs rejected successfully!']);
    }

    public function exportExcel(Request $request)
    {
        // dd($request->all);
        $query = PurchaseOrder::query();

        // Apply filters if provided
        if ($request->filled('po_number')) {
            $query->where('po_number', 'LIKE', '%'.$request->po_number.'%');
        }
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'LIKE', '%'.$request->vendor_name.'%');
        }
        if ($request->filled('invoice_date')) {
            $query->whereDate('invoice_date', $request->invoice_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // // Debugging the SQL query
        // dd($query->toSql(), $query->getBindings());

        $filteredData = $query->get();

        return Excel::download(new PurchaseOrderExport($filteredData), 'purchase_orders.xlsx');
    }

    public function edit($id)
    {
        $categories = PurchaseOrderCategory::all();
        $po = PurchaseOrder::find($id);

        return view('purchase_order.edit', compact('po', 'categories'));
    }

    public function update(UpdatePoRequest $request, $id)
    {
        // Validate the request (already done automatically by UpdatePoRequest)
        $validatedData = $request->validated();

        // Find the existing PO
        $po = PurchaseOrder::findOrFail($id);

        // Update the PO with validated data
        $po->po_number = $validatedData['po_number'];
        $po->vendor_name = $validatedData['vendor_name'];
        $po->invoice_date = $validatedData['invoice_date'];
        $po->invoice_number = $validatedData['invoice_number'];
        $po->tanggal_pembayaran = $validatedData['tanggal_pembayaran'];
        $po->currency = $validatedData['currency'];
        $po->purchase_order_category_id = $validatedData['purchase_order_category_id'];
        $po->total = str_replace(',', '', $validatedData['total']); // Remove commas from total

        // Check if a new PDF file is uploaded
        if ($request->hasFile('pdf_file')) {
            // Delete the old file if necessary (optional, depends on your setup)
            if ($po->filename) {
                Storage::delete($po->filename);
            }

            $file = $validatedData['pdf_file'];
            $filename = 'PO_'.$po->po_number.'_'.time().'.pdf';
            $file->storeAs('public/pdfs', $filename);

            // Store the new file and update the path
            $po->filename = $filename;
        }

        // Save the changes
        $po->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'PO Successfully Updated!');
    }

    public function dashboard(Request $request)
    {
        // Determine the current month in 'YYYY-MM' format
        $currentMonth = now()->format('Y-m');

        // Get the selected month from the request (default to the current month)
        $selectedMonth = $request->get('month', $currentMonth);

        // Query for vendor totals (distinct vendors with their totals)
        $vendorTotals = PurchaseOrder::selectRaw(
            'vendor_name, COUNT(id) as po_count, SUM(total) as total',
        )
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$selectedMonth])
            ->groupBy('vendor_name')
            ->orderByDesc('total')
            ->get();

        // Fetch top 5 vendors
        $topVendors = PurchaseOrder::selectRaw('vendor_name')
            ->selectRaw('SUM(total) as total')
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$selectedMonth])
            ->groupBy('vendor_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Sum of totals for each month (for chart)
        $monthlyTotals = PurchaseOrder::selectRaw(
            "DATE_FORMAT(invoice_date, '%Y-%m') as month, SUM(total) as total",
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // List of available months for the filter dropdown
        $availableMonths = PurchaseOrder::selectRaw("DATE_FORMAT(invoice_date, '%Y-%m') as month")
            ->distinct()
            ->orderByDesc('month')
            ->pluck('month');

        // Fetch counts for approved, waiting, and rejected using query scopes
        $statusCounts = [
            'approved' => PurchaseOrder::approved()->count(),
            'waiting' => PurchaseOrder::waiting()->count(),
            'rejected' => PurchaseOrder::rejected()->count(),
            'canceled' => PurchaseOrder::canceled()->count(),
        ];

        // Fetch Purchase Order counts grouped by category
        $poByCategory = PurchaseOrder::selectRaw('purchase_order_category_id, COUNT(*) as count')
            ->groupBy('purchase_order_category_id')
            ->get();

        // Fetch category names for better readability
        $categories = PurchaseOrderCategory::whereIn(
            'id',
            $poByCategory->pluck('purchase_order_category_id'),
        )->pluck('name', 'id'); // Returns [id => name]

        // Format data for chart
        $categoryChartData = $poByCategory->map(function ($po) use ($categories) {
            return [
                'label' => $categories[$po->purchase_order_category_id] ?? 'Unknown',
                'count' => $po->count,
            ];
        });

        return view(
            'purchase_order.dashboard',
            compact(
                'monthlyTotals',
                'topVendors',
                'vendorTotals',
                'availableMonths',
                'selectedMonth',
                'statusCounts',
                'categoryChartData',
            ),
        );
    }

    public function filter(Request $request)
    {
        $selectedMonth = $request->get('month');
        // Base query for purchase orders filtered by the selected month
        $query = PurchaseOrder::query();

        if ($selectedMonth) {
            $query->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$selectedMonth]);
        }

        // Query for vendor totals (all vendors with their total amounts)
        $vendorTotals = $query
            ->selectRaw('vendor_name, COUNT(id) as po_count, SUM(total) as total')
            ->groupBy('vendor_name')
            ->orderByDesc('total')
            ->get();

        // Fetch top 5 vendors
        $topVendors = $query
            ->select('vendor_name')
            ->selectRaw('SUM(total) as total')
            ->groupBy('vendor_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        // Data for the chart
        $monthlyTotals = PurchaseOrder::selectRaw(
            "DATE_FORMAT(invoice_date, '%Y-%m') as month, SUM(total) as total",
        )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json([
            'chartData' => [
                'labels' => $monthlyTotals->pluck('month'),
                'totals' => $monthlyTotals->pluck('total'),
            ],
            'topVendors' => $topVendors,
            'vendorTotals' => $vendorTotals,
        ]);
    }

    public function vendorMonthlyTotals(Request $request)
    {
        $vendorName = $request->get('vendor');

        if (! $vendorName) {
            return response()->json(['error' => 'Vendor name is required'], 400);
        }

        // Query for monthly totals for the specified vendor
        $monthlyTotals = PurchaseOrder::selectRaw(
            "DATE_FORMAT(invoice_date, '%Y-%m') as month, SUM(total) as total",
        )
            ->where('vendor_name', $vendorName)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return response()->json($monthlyTotals);
    }

    public function getVendorDetails(Request $request)
    {
        $vendorName = $request->query('vendor');
        $selectedMonth = $request->query('month'); // Format: 'YYYY-MM'

        $purchaseOrders = PurchaseOrder::where('vendor_name', $vendorName)
            ->select('id', 'po_number', 'invoice_date', 'total', 'status')
            ->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$selectedMonth]) // Filter by selected month
            ->orderBy('invoice_date', 'desc')
            ->orderByDesc('total')
            ->get();

        return response()->json($purchaseOrders);
    }

    public function cancel(Request $request, $id)
    {
        $cancelReason = $request->description;
        PurchaseOrder::find($id)->update([
            'reason' => $cancelReason,
            'status' => 4,
            'approved_date' => null,
        ]);

        return redirect()->back()->with('success', 'Purchase Order cancelled successfully!');
    }
}
