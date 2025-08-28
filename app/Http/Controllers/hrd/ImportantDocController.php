<?php

namespace App\Http\Controllers\hrd;

use App\DataTables\ImportantDocumentDataTable;
use Illuminate\Http\Request;
use App\Models\hrd\ImportantDoc;
use App\Models\hrd\ImportantDocType;
use App\Http\Controllers\Controller;
use App\Models\hrd\ImportantDocFile;
use Dompdf\Dompdf;

class ImportantDocController extends Controller
{
    public function index(ImportantDocumentDataTable $dataTable)
    {
        $importantDocs = ImportantDoc::with("type", "files")->orderBy("expired_date")->get();
        return $dataTable->render("hrd.importantDocs.index", compact("importantDocs"));
    }

    /**
     * Return a create view
     */

    public function create()
    {
        $types = ImportantDocType::all()->reverse();
        return view("hrd.importantDocs.create", compact("types"));
    }

    /**
     * Store a new importantDoc in the database.
     */
    public function store(Request $request)
    {
        // Validate form
        $request->validate([
            "name" => "required|max:255",
            "type_id" => "required",
            "expired_date" => "required",
            "files.*" => "file|max:2048|nullable",
            "document_id" => "string|max:255|nullable",
            "description" => "string|max:255|nullable",
        ]);

        // Create a new ImportantDoc instance with additional data
        $importantDoc = ImportantDoc::create([
            "name" => $request->name,
            "type_id" => $request->type_id,
            "expired_date" => $request->expired_date,
            "document_id" => $request->document_id,
            "description" => $request->description,
        ]);

        if ($request->hasFile("files")) {
            // dd($request->file('files'));
            foreach ($request->file("files") as $file) {
                // Generate a unique filename
                $fileName = time() . "-" . $file->getClientOriginalName();

                // Read file content
                $fileData = file_get_contents($file->getRealPath());

                // Store the file in the filesystem
                $file->storeAs("public/importantDocuments", $fileName);

                // Store file data in the database
                $importantDoc->files()->create([
                    "name" => $fileName,
                    "mime_type" => $file->getClientMimeType(),
                    "data" => $fileData,
                ]);
            }
        }

        return redirect()
            ->route("hrd.importantDocs.index")
            ->with("success", "Data berhasil dibuat!");
    }

    public function detail($id)
    {
        $importantDoc = ImportantDoc::find($id);
        return view("hrd.importantDocs.detail", compact("importantDoc"));
    }

    public function edit($id)
    {
        $types = ImportantDocType::all()->reverse();
        $importantDoc = ImportantDoc::with("type")->find($id);
        return view("hrd.importantDocs.edit", compact("importantDoc", "types"));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "name" => "required|max:255",
            "type_id" => "required|max:255",
            "expired_date" => "required",
        ]);

        $importantDoc = ImportantDoc::find($id);

        if ($importantDoc) {
            $importantDoc->update($request->all());
            return redirect()
                ->route("hrd.importantDocs.index")
                ->with("success", "Data berhasil diupdate!");
        } else {
            return redirect()->route("hrd.importantDocs.index")->with("error", "Data not found!");
        }
    }

    public function destroy($id)
    {
        ImportantDoc::find($id)->delete();
        return redirect()
            ->route("hrd.importantDocs.index")
            ->with("success", "Data berhasil dihapus!");
    }

    public function downloadFile(ImportantDocFile $file)
    {
        return response()->streamDownload(function () use ($file) {
            echo $file->data;
        }, $file->name);
    }

    public function previewPdf($file)
    {
        // Retrieve the document from the database
        $document = $file;

        dd($document);

        // Initialize Dompdf
        $dompdf = new Dompdf();

        // Load the PDF content
        $dompdf->loadHtml($document->content);

        // (Optional) Set paper size and orientation
        $dompdf->setPaper("A4", "portrait");

        // Render the PDF
        $dompdf->render();

        // Output the PDF content to the browser
        return $dompdf->stream($document->name);
    }
}
