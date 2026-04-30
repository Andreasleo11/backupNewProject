<?php

namespace App\Http\Controllers;

use App\Application\Approval\Contracts\Approvals;
use App\DataTables\PurchaseOrderDataTable;
use App\Exports\PurchaseOrderExport;
use App\Http\Requests\StorePoRequest;
use App\Http\Requests\UpdatePoRequest;
use App\Models\File;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderCategory;
use App\Models\PurchaseOrderDownloadLog;
use App\Services\PdfProcessingService;
use App\Services\PurchaseOrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PurchaseOrderController extends Controller
{
    public function __construct(
        private PurchaseOrderService $poService,
        private PdfProcessingService $pdfService,
        private Approvals $approvals
    ) {}

    public function index(PurchaseOrderDataTable $dataTable, Request $request)
    {
        $month = $request->query('month');

        return $dataTable->with(['month' => $month])->render('purchase_order.index');
    }

    public function approveSelected(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (! $ids || ! is_array($ids)) {
                return response()->json(['message' => 'Invalid request: No POs selected.'], 400);
            }

            $this->poService->approveAll($ids, auth()->id());

            return response()->json(['message' => 'Selected purchase orders approval processed.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to approve selection: ' . $e->getMessage()], 500);
        }
    }

    public function rejectSelected(Request $request)
    {
        try {
            $ids = $request->input('ids');
            $reason = $request->input('reason');

            if (! $ids || ! is_array($ids) || ! $reason) {
                return response()->json(['message' => 'Invalid request: IDs and reason are required.'], 400);
            }

            $this->poService->rejectAll($ids, auth()->id(), $reason);

            return response()->json(['message' => 'Selected purchase orders rejection processed.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to reject selection: ' . $e->getMessage()], 500);
        }
    }

    public function create(Request $request)
    {
        $categories = PurchaseOrderCategory::all();
        $parentPONumber = $request->get('parent_po_number', null);

        return view('purchase_order.create', compact('categories', 'parentPONumber'));
    }

    public function store(StorePoRequest $request)
    {
        try {
            // Process validated data
            $validated = $request->validated();

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
            $filename = 'PO_' . $validated['po_number'] . '_' . time() . '.pdf';
            $file->storeAs('public/pdfs', $filename);

            // Remove commas from the total and convert it to a float
            $validated['total'] = (float) str_replace(',', '', $validated['total']);

            // Add the processed filename to validated data
            $validated['pdf_file'] = $filename;

            // Use the service to create the PO
            $purchaseOrder = $this->poService->create($validated);

            return redirect()->route('po.index')->with('success', 'PO created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create PO via controller', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to create purchase order. Please try again.');
        }
    }

    public function view($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'user', 
            'category', 
            'approvalRequest.actions.causer', 
            'approvalRequest.steps', 
            'downloadLogs.user',
            'latestDownloadLog.user'
        ])->findOrFail($id);

        $revisions = PurchaseOrder::where('parent_po_number', $purchaseOrder->po_number)->get();

        $user = Auth::user();
        $files = File::where('doc_id', $purchaseOrder->po_number)->get();
        $director = $user->hasRole('director');

        // Check if the PDF exists in storage
        if (! Storage::exists('public/pdfs/' . $purchaseOrder->filename)) {
            // abort(500, 'PDF file not found.');
        }
        
        return view(
            'purchase_order.view',
            compact('purchaseOrder', 'user', 'files', 'revisions', 'director'),
        );
    }

    public function sign(Request $request)
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $id = $request->input('id');
                $po = PurchaseOrder::findOrFail($id);

                // Process approval via approval engine first
                // This ensures if approval logic fails, the PDF is never signed
                $this->approvals->approve($po, auth()->id(), 'Signed and approved via PDF signature');

                // Use the PDF service to sign the document
                // This updates the filename and saves the PO record
                $this->pdfService->sign($po, auth()->id());

                return response()->json(['message' => 'PDF signed and approved successfully!']);
            });
        } catch (\Exception $e) {
            Log::error('PDF signing/approval failed in controller', [
                'po_id' => $request->input('id'),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to sign PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function rejectPDF(Request $request)
    {
        try {
            $po = PurchaseOrder::findOrFail($request->input('id'));
            $reason = $request->input('reason', 'No reason provided');

            // Use the PDF service to reject the document
            $this->pdfService->reject($po, $reason);

            return response()->json(['message' => 'PO rejected successfully.']);
        } catch (\Exception $e) {
            Log::error('PDF rejection failed in controller', [
                'po_id' => $request->input('id'),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to reject PO: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function downloadPDF($id)
    {
        try {
            // Use the PDF service for download with security checks
            $response = $this->pdfService->download($id, auth()->id());

            // Log download for creator (additional logging beyond service)
            $po = PurchaseOrder::findOrFail($id);
            if ($po->creator_id === auth()->id()) {
                PurchaseOrderDownloadLog::create([
                    'user_id' => auth()->id(),
                    'purchase_order_id' => $po->id,
                ]);
            }

            return $response;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Purchase order not found.'], 404);
        } catch (\Exception $e) {
            Log::error('PDF download failed in controller', [
                'po_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->poService->delete($id);

            return redirect()->route('po.index')->with('success', 'PO deleted successfully!');
        } catch (\Exception $e) {
            Log::error("Error deleting PO with ID {$id}: " . $e->getMessage());

            return redirect()
                ->route('po.index')
                ->with(
                    'error',
                    'An error occurred while trying to delete the PO: ' . $e->getMessage(),
                );
        }
    }

    public function rejectAll(Request $request)
    {
        try {
            $ids = $request->input('ids');
            $reason = $request->input('reason', 'No reason provided');

            if (! $ids || ! is_array($ids)) {
                return response()->json(['message' => 'Invalid request: No POs selected.'], 400);
            }

            // Use the service to reject all selected POs
            $this->poService->rejectAll($ids, auth()->id(), $reason);

            return response()->json(['message' => 'All selected purchase orders rejection processed.']);
        } catch (\Exception $e) {
            Log::error('Bulk rejection failed in controller', [
                'ids' => $request->input('ids'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to reject purchase orders: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        // dd($request->all);
        $query = PurchaseOrder::query();

        // Apply filters if provided
        if ($request->filled('po_number')) {
            $query->where('po_number', 'LIKE', '%' . $request->po_number . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'LIKE', '%' . $request->vendor_name . '%');
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
        try {
            // Validate the request (already done automatically by UpdatePoRequest)
            $validatedData = $request->validated();

            // Handle PDF file upload if provided
            if ($request->hasFile('pdf_file')) {
                $file = $validatedData['pdf_file'];
                $filename = 'PO_' . $validatedData['po_number'] . '_' . time() . '.pdf';
                $file->storeAs('public/pdfs', $filename);
                $validatedData['pdf_file'] = $filename;
            }

            // Remove commas from total
            $validatedData['total'] = str_replace(',', '', $validatedData['total']);

            // Use the service to update the PO
            $updatedPo = $this->poService->update($id, $validatedData);

            // Redirect back with a success message
            return redirect()->back()->with('success', 'PO Successfully Updated!');
        } catch (\Exception $e) {
            Log::error('Failed to update PO via controller', [
                'po_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update purchase order. Please try again.');
        }
    }

    public function dashboard(Request $request)
    {
        try {
            $month = $request->get('month');
            $data = $this->poService->getDashboardData($month);

            return view('purchase_order.dashboard', $data);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load dashboard: ' . $e->getMessage());
        }
    }

    public function filter(Request $request)
    {
        try {
            $month = $request->get('month');
            $data = $this->poService->getDashboardData($month);

            return response()->json([
                'chartData' => [
                    'labels' => $data['monthlyTotals']->pluck('month'),
                    'totals' => $data['monthlyTotals']->pluck('total'),
                ],
                'topVendors' => $data['topVendors'],
                'vendorTotals' => $data['vendorTotals'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function vendorMonthlyTotals(Request $request)
    {
        $vendorName = $request->get('vendor');

        if (! $vendorName) {
            return response()->json(['error' => 'Vendor name is required'], 400);
        }

        // This could also be moved to the service if complex, but keeping simple for now
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
        try {
            $vendorName = $request->query('vendor');
            $selectedMonth = $request->query('month');

            $purchaseOrders = $this->poService->getVendorDetails($vendorName, $selectedMonth);

            return response()->json($purchaseOrders);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        try {
            $reason = $request->input('description', 'No reason provided');
            $this->poService->cancel($id, $reason);

            return redirect()->back()->with('success', 'Purchase Order cancelled successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to cancel PO: ' . $e->getMessage());
        }
    }
}
