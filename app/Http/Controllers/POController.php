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
        $masterPO->approved_date = Carbon::now();
        $masterPO->status = 1; // Initial status
        $masterPO->filename = $filename;
        $masterPO->save();

        // Redirect to the PDF viewer
        return redirect()->route('masterpo.index');
    }

    public function viewPDF($id)
    {
        $data = MasterPO::find($id);

        $filename = $data->filename;
        // Check if the PDF exists in storage
        if (!Storage::exists('public/pdfs/' . $data->filename)) {
            abort(404, 'PDF file not found.');
        }

        return view('masterpo.viewpo', ['filename' => $filename,
    'id' => $id]);
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

    // view buat controller pake signature 
    //     <!-- resources/views/viewpo.blade.php -->
    // @extends('layouts.app')

    // @section('content')
    // <div class="container my-5">
    //     <h1 class="text-3xl font-bold mb-4">View and Sign PO File</h1>

    //     <div class="card shadow-sm p-4 mb-4">
    //         <!-- PDF Display -->
    //         <iframe src="{{ asset('storage/pdfs/' . $filename) }}" width="100%" height="600px"></iframe>
    //     </div>

    //     <!-- Signature Canvas -->
    //     <div class="mt-4">
    //         <p>Draw your signature below:</p>
    //         <canvas id="signatureCanvas" width="500" height="200" style="border: 1px solid #ccc;"></canvas>
    //         <button id="clearCanvas" class="btn btn-danger mt-2">Clear Signature</button>
    //     </div>

    //     <!-- Save Signature Button -->
    //     <button id="saveSignature" class="btn btn-primary mt-4">Save Signature to PDF</button>
    // </div>

    // <script>
    //     // Set up the canvas
    //     const canvas = document.getElementById('signatureCanvas');
    //     const ctx = canvas.getContext('2d');
    //     let isDrawing = false;

    //     // Set up event listeners for drawing
    //     canvas.addEventListener('mousedown', (event) => {
    //         isDrawing = true;
    //         ctx.beginPath();
    //         ctx.moveTo(event.offsetX, event.offsetY);
    //     });

    //     canvas.addEventListener('mousemove', (event) => {
    //         if (isDrawing) {
    //             ctx.lineTo(event.offsetX, event.offsetY);
    //             ctx.stroke();
    //         }
    //     });

    //     canvas.addEventListener('mouseup', () => {
    //         isDrawing = false;
    //     });

    //     canvas.addEventListener('mouseleave', () => {
    //         isDrawing = false;
    //     });

    //     // Clear the canvas
    //     document.getElementById('clearCanvas').addEventListener('click', () => {
    //         ctx.clearRect(0, 0, canvas.width, canvas.height);
    //     });

    //     // Save Signature to PDF
    //     document.getElementById('saveSignature').addEventListener('click', function () {
    //         const dataUrl = canvas.toDataURL('image/png');

    //         // Send the signature data and PDF filename to the server
    //         fetch('{{ route("pdf.sign") }}', {
    //             method: 'POST',
    //             headers: {
    //                 'Content-Type': 'application/json',
    //                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
    //             },
    //             body: JSON.stringify({
    //                 signature: dataUrl,
    //                 filename: '{{ $filename }}'
    //             })
    //         })
    //         .then(response => response.json())
    //         .then(data => {
    //             alert(data.message);
    //             window.location.reload(); // Reload the page to show the signed PDF
    //         })
    //         .catch(error => console.error('Error:', error));
    //     });
    // </script>
    // @endsection


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
