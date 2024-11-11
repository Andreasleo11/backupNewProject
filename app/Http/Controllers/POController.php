<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\MasterPO;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class POController extends Controller
{
    public function index()
    {
        $purchaseOrdersQuery = MasterPO::query();

        $director = auth()->user()->department->name === 'DIRECTOR';
        $notAdminUsers = auth()->user()->role->name !== 'SUPERADMIN' && !$director;

        if($notAdminUsers) {
            $purchaseOrdersQuery->where('status', '!=', 1)->where('creator_id', auth()->user()->id);
        }

        $data = $purchaseOrdersQuery->get();

        return view('masterpo.index', compact('data'));
    }

    public function create()
    {
        return view('masterpo.create');
    }

    public function store(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'po_number' => 'required|integer',
            'pdf_file' => 'required|mimes:pdf|max:10240' // Max 10 MB
        ]);

        // Store the uploaded PDF
        $file = $request->file('pdf_file');
        $filename = Str::random(20) . '.pdf';
        $filePath = $file->storeAs('public/pdfs', $filename);

        // Create a new MasterPO record using Eloquent
        $masterPO = new MasterPO();
        $masterPO->po_number = $request->input('po_number');
        $masterPO->status = 1; // Initial status
        $masterPO->filename = $filename;
        $masterPO->creator_id = auth()->user()->id;
        $masterPO->save();

        // Redirect to the PDF viewer
        return redirect()->route('po.index');
    }

    public function view($id)
    {
        $purchaseOrder = MasterPO::find($id);

        $filename = $purchaseOrder->filename;
        // Check if the PDF exists in storage
        if (!Storage::exists('public/pdfs/' . $purchaseOrder->filename)) {
            abort(404, 'PDF file not found.');
        }

        return view('masterpo.view', compact('purchaseOrder'));
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


}
