<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use App\Models\MasterPO;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;


class POController extends Controller
{
    public function index()
    {
        $datas = MasterPO::get();

        return view('masterpo.index', compact('datas'));
    }

    public function uploadview()
    {
        return view('masterpo.poupload');
    }

    public function upload(Request $request)
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
        $masterPO->save();

        // Redirect to the PDF viewer
        return redirect()->route('po.index');
    }

    public function viewPDF($id)
    {
        $data = MasterPO::find($id);

        $filename = $data->filename;
        // Check if the PDF exists in storage
        if (!Storage::exists('public/pdfs/' . $data->filename)) {
            abort(404, 'PDF file not found.');
        }

        return view('masterpo.viewpo', compact('data'));
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

    public function downloadPDF($filename)
    {
        $path = storage_path("app/public/pdfs/{$filename}");
        if (!file_exists($path)) {
            abort(404, 'PDF file not found.');
        }

        return response()->download($path, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }



}
