<?php

namespace App\Http\Controllers;

use App\Exports\PurchaseOrderExport;
use App\Http\Requests\StorePoRequest;
use App\Http\Requests\UpdatePoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\MasterPO;
use App\Models\File;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class POController extends Controller
{
    public function index()
    {
        $purchaseOrdersQuery = MasterPO::query();

        $director = auth()->user()->department->name === 'DIRECTOR';
        $notAdminUsers = auth()->user()->role->name !== 'SUPERADMIN' && !$director;
        $accountingUser = auth()->user()->department->name === 'ACCOUNTING';

        if($accountingUser){
            $purchaseOrdersQuery->where('status', 2);
        }elseif($notAdminUsers) {
            $purchaseOrdersQuery->where('status', '!=', 1)->where('creator_id', auth()->user()->id);
        }

        $data = $purchaseOrdersQuery->get();

        return view('masterpo.index', compact('data'));
    }

    public function create()
    {
        return view('masterpo.create');
    }

    public function store(StorePoRequest $request)
    {
        // Process validated data
        $validated = $request->validated();

        // Convert po_date from 'dd.mm.yy' to 'yyyy-mm-dd'
        if (isset($validated['po_date'])) {
            $date = \DateTime::createFromFormat('d.m.y', $validated['po_date']);
            if ($date) {
                $validated['po_date'] = $date->format('Y-m-d');
            } else {
                return redirect()->back()->withInputs(['po_date' => 'Invalid date format']);
            }
        }

        // Store the uploaded PDF with a unique filename
        $file = $validated['pdf_file'];
        $filename = 'PO_' . Str::random(10) . '_' . time() . '.pdf';
        $filePath = $file->storeAs('public/pdfs', $filename);

        // Remove commas from the total and convert it to a float
        $total = (float) str_replace(',', '', $validated['total']);

        // Create a new MasterPO record using Eloquent
        $masterPO = new MasterPO();
        $masterPO->po_number = $validated['po_number'];
        $masterPO->status = 1; // Initial status
        $masterPO->filename = $filename;
        $masterPO->creator_id = auth()->id();
        $masterPO->vendor_name = $validated['vendor_name'];
        $masterPO->po_date = $validated['po_date'];
        $masterPO->currency = $validated['currency'];
        $masterPO->total = $total;
        $masterPO->tanggal_pembayaran = $validated['tanggal_pembayaran'];
        $masterPO->save();

        // Redirect to the PDF viewer with a success message
        return redirect()->route('po.index')->with('success', 'PO created successfully.');
    }

    public function view($id)
    {
        $purchaseOrder = MasterPO::find($id);

        $user = Auth::user();
        $files = File::where('doc_id', $purchaseOrder->po_number)->get();

        $filename = $purchaseOrder->filename;
        // Check if the PDF exists in storage
        if (!Storage::exists('public/pdfs/' . $purchaseOrder->filename)) {
            abort(404, 'PDF file not found.');
        }

        return view('masterpo.view', compact('purchaseOrder', 'user', 'files'));
    }

    // public function signPDF(Request $request) version pake signature box
    // {

    //     // Load the original PDF
    // $filename = $request->input('filename');
    // $pdfPath = public_path("storage/pdfs/{$filename}");
    // $signedPdfPath = str_replace('.pdf', '_signed.pdf', $pdfPath);

    // // Initialize FPDI
    // $pdf = new Fpdi();
    // $pageCount = $pdf->setSourceFile($pdfPath);

    // // Path to the stored signature file
    // $signaturePath = public_path('storage/autographs/Djoni.png');

    // // Loop through each page and add it to the new PDF
    // for ($pageIndex = 1; $pageIndex <= $pageCount; $pageIndex++) {
    //     $pdf->AddPage();
    //     $templateId = $pdf->importPage($pageIndex);
    //     $pdf->useTemplate($templateId, 0, 0, 210);

    //     // Check if this is the last page
    //     if ($pageIndex === $pageCount) {
    //         $pdf->SetFont('Arial', '', 12); // Set font before adding text if necessary
    //         $pdf->Image($signaturePath, 40, 250, 40, 20); // Position the signature on the last page
    //     }
    // }

    // // Save the signed PDF
    // $pdf->Output($signedPdfPath, 'F');

    // return response()->json(['message' => 'PDF signed successfully!']);
    // }


    public function signPDF(Request $request) // version click langsung keluar tanda tangan
    {
        $id = $request->input('id');
            // Load the original PDF
        $filename = $request->input('filename');
        $pdfPath = public_path("storage/pdfs/{$filename}");
        $signedPdfPath = str_replace('.pdf', '_signed.pdf', $pdfPath);

        // Initialize FPDI
        $pdf = new Fpdi();
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

        $masterPO = MasterPO::find($id);
        if ($masterPO) {
            $masterPO->filename = basename($signedPdfPath); // Save only the file name, not the full path
            $masterPO->approved_date = now();
            $masterPO->status = 2;
            $masterPO->save();
        }

        return response()->json(['message' => 'PDF signed successfully!']);
    }

    public function rejectPDF(Request $request)
    {
        $data = MasterPO::find($request->input('id'));

        if (!$data) {
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
            // Attempt to find the MasterPO record
            $po = MasterPO::findOrFail($id);

            $filename = $po->filename;
            $path = storage_path("app/public/pdfs/{$filename}");

            // Check if the file exists in the specified path
            if (!file_exists($path)) {
                abort(404, 'PDF file not found.');
            }

            // Update the 'downloaded_at' timestamp for the PO
            $po->update(['downloaded_at' => now()]);

            // Return the response to download the PDF file
            return response()->download($path, $filename, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the MasterPO record is not found
            return response()->json(['error' => 'Purchase order not found.'], 404);
        } catch (\Exception $e) {
            // Catch any other exceptions and return a generic error response
            return response()->json(['error' => 'An error occurred while downloading the PDF.'], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $po = MasterPO::find($id);

            if (!$po) {
                return redirect()->route('po.index')->with('error', 'PO not found!');
            }

            $po->delete();
            return redirect()->route('po.index')->with('success', 'PO deleted successfully!');
        } catch (\Exception $e) {
            // Log the exception message for debugging
            Log::error("Error deleting PO with ID {$id}: " . $e->getMessage());

            return redirect()->route('po.index')->with('error', 'An error occurred while trying to delete the PO. Please try again later.');
        }
    }

    public function signAll(Request $request)
    {
        $ids = $request->input('ids');

        // Fetch all requested PO records and separate by 'APPROVED' and 'REJECTED' statuses
        $approvedPOs = MasterPO::whereIn('id', $ids)->where('status', 2)->pluck('po_number');
        $rejectedPOs = MasterPO::whereIn('id', $ids)->where('status', 3)->pluck('po_number');

        // If any approved or rejected POs are found, return an error
        if ($approvedPOs->isNotEmpty() || $rejectedPOs->isNotEmpty()) {
            $message = 'Cannot sign selected POs. ';

            if ($approvedPOs->isNotEmpty()) {
                $message .= 'The following PO Numbers are already approved: ' . $approvedPOs->join(', ') . '. ';
            }

            if ($rejectedPOs->isNotEmpty()) {
                $message .= 'The following PO Numbers are already rejected: ' . $rejectedPOs->join(', ') . '.';
            }

            return response()->json(['message' => $message], 400);
        }

        // Proceed to sign POs if no conflicts
        foreach ($ids as $id) {
            $this->signPDF(new Request(['id' => $id, 'filename' => MasterPO::find($id)->filename]));
        }

        return response()->json(['message' => 'All selected POs signed successfully!']);
    }

    public function rejectAll(Request $request)
    {
        $ids = $request->input('ids');
        $reason = $request->input('reason', 'No reason provided');

        // Fetch all requested PO records and separate by 'APPROVED' and 'REJECTED' statuses
        $approvedPOs = MasterPO::whereIn('id', $ids)->where('status', 2)->pluck('po_number');
        $rejectedPOs = MasterPO::whereIn('id', $ids)->where('status', 3)->pluck('po_number');

        // If any approved or rejected POs are found, return an error
        if ($approvedPOs->isNotEmpty() || $rejectedPOs->isNotEmpty()) {
            $message = 'Cannot reject selected POs. ';

            if ($approvedPOs->isNotEmpty()) {
                $message .= 'The following PO Numbers are already approved: ' . $approvedPOs->join(', ') . '. ';
            }

            if ($rejectedPOs->isNotEmpty()) {
                $message .= 'The following PO Numbers are already rejected: ' . $rejectedPOs->join(', ') . '.';
            }

            return response()->json(['message' => $message], 400);
        }

        // Proceed to reject POs if no conflicts
        MasterPO::whereIn('id', $ids)->update([
            'status' => 3,
            'reason' => $reason
        ]);

        return response()->json(['message' => 'All selected POs rejected successfully!']);
    }

    public function exportExcel(Request $request)
    {
        // dd($request->all);
        $query = MasterPO::query();

        // Apply filters if provided
        if ($request->filled('po_number')) {
            $query->where('po_number', 'LIKE', '%' . $request->po_number . '%');
        }
        if ($request->filled('vendor_name')) {
            $query->where('vendor_name', 'LIKE', '%' . $request->vendor_name . '%');
        }
        if ($request->filled('po_date')) {
            $query->whereDate('po_date', $request->po_date);
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
        $po = MasterPO::find($id);

        return view('masterpo.edit', compact('po'));
    }

    public function update(UpdatePoRequest $request, $id)
    {
        // Validate the request (already done automatically by UpdatePoRequest)
        $validatedData = $request->validated();

        // Find the existing PO
        $po = MasterPO::findOrFail($id);

        // Update the PO with validated data
        $po->po_number = $validatedData['po_number'];
        $po->vendor_name = $validatedData['vendor_name'];
        $po->po_date = $validatedData['po_date'];
        $po->tanggal_pembayaran = $validatedData['tanggal_pembayaran'];
        $po->currency = $validatedData['currency'];
        $po->total = str_replace(',', '', $validatedData['total']); // Remove commas from total

        // Check if a new PDF file is uploaded
        if ($request->hasFile('pdf_file')) {
            // Delete the old file if necessary (optional, depends on your setup)
            if ($po->pdf_file) {
                Storage::delete($po->pdf_file);
            }

            $file = $validatedData['pdf_file'];
            $filename = 'PO_' . Str::random(10) . '_' . time() . '.pdf';
            $filePath = $file->storeAs('public/pdfs', $filename);

            // Store the new file and update the path
            $po->filename = $filename;
        }

        // Save the changes
        $po->save();

        // Redirect back with a success message
        return redirect()->route('po.index')->with('success', 'PO Successfully Updated!');
    }
}
