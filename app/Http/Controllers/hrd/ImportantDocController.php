<?php

namespace App\Http\Controllers\hrd;

use App\DataTables\ImportantDocumentDataTable;
use App\Http\Controllers\Controller;
use App\Models\hrd\ImportantDoc;
use App\Models\hrd\ImportantDocFile;
use App\Models\hrd\ImportantDocType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportantDocController extends Controller
{
    public function index(ImportantDocumentDataTable $dataTable, Request $request)
    {
        $threshold = $request->get('threshold', 2);
        $today = now()->startOfDay();
        $warningDate = now()->addMonths($threshold)->endOfDay();
        $thresholdDays = $today->diffInDays($warningDate);

        $stats = [
            'total'         => ImportantDoc::count(),
            'active'        => ImportantDoc::where('expired_date', '>', $warningDate)->count(),
            'expiring_soon' => ImportantDoc::whereBetween('expired_date', [$today, $warningDate])->count(),
            'expired'       => ImportantDoc::where('expired_date', '<', $today)->count(),
        ];

        $types = ImportantDocType::all();

        $dataTable->thresholdDays = $thresholdDays;
        $dataTable->threshold = $threshold; // Still needed for the UI dropdown state
        $dataTable->today = $today;

        return $dataTable->render('hrd.importantDocs.index', compact('stats', 'threshold', 'types', 'thresholdDays'));
    }

    /**
     * Return a create view
     */
    public function create()
    {
        $types = ImportantDocType::all()->reverse();

        return view('hrd.importantDocs.create', compact('types'));
    }

    /**
     * Store a new importantDoc in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|max:255',
            'type_id'     => 'required',
            'expired_date'=> 'required',
            'files.*'     => 'file|max:2048|nullable',
            'document_id' => 'string|max:255|nullable',
            'description' => 'string|max:255|nullable',
        ]);

        $importantDoc = ImportantDoc::create([
            'name'         => $request->name,
            'type_id'      => $request->type_id,
            'expired_date' => $request->expired_date,
            'document_id'  => $request->document_id,
            'description'  => $request->description,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '-' . $file->getClientOriginalName();
                $fileData = file_get_contents($file->getRealPath());

                $file->storeAs('public/importantDocuments', $fileName);

                $importantDoc->files()->create([
                    'name'      => $fileName,
                    'mime_type' => $file->getClientMimeType(),
                    'data'      => $fileData,
                ]);
            }
        }

        return redirect()
            ->route('hrd.importantDocs.index')
            ->with('success', 'Data berhasil dibuat!');
    }

    public function detail($id)
    {
        $importantDoc = ImportantDoc::with('type', 'files')->findOrFail($id);
        $threshold = 2; // Default for context
        $today = now()->startOfDay();
        $warningDate = now()->addMonths($threshold)->endOfDay();
        $thresholdDays = $today->diffInDays($warningDate);

        if (request()->ajax()) {
            return view('hrd.importantDocs.partials.detail_content', compact('importantDoc', 'thresholdDays', 'today'));
        }

        return view('hrd.importantDocs.detail', compact('importantDoc', 'thresholdDays', 'today'));
    }

    public function edit($id)
    {
        $types        = ImportantDocType::all()->reverse();
        $importantDoc = ImportantDoc::with('type', 'files')->findOrFail($id);
        $threshold = 2; // Default for context
        $today = now()->startOfDay();
        $warningDate = now()->addMonths($threshold)->endOfDay();
        $thresholdDays = $today->diffInDays($warningDate);

        return view('hrd.importantDocs.edit', compact('importantDoc', 'types', 'thresholdDays', 'today'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'         => 'required|max:255',
            'type_id'      => 'required',
            'expired_date' => 'required',
            'document_id'  => 'nullable|string|max:255',
            'description'  => 'nullable|string|max:255',
            'files.*'      => 'nullable|file|max:2048',
        ]);

        $importantDoc = ImportantDoc::findOrFail($id);

        $importantDoc->update([
            'name'         => $request->name,
            'type_id'      => $request->type_id,
            'expired_date' => $request->expired_date,
            'document_id'  => $request->document_id,
            'description'  => $request->description,
        ]);

        // Append any newly uploaded files
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $fileName = time() . '-' . $file->getClientOriginalName();
                $fileData = file_get_contents($file->getRealPath());

                $file->storeAs('public/importantDocuments', $fileName);

                $importantDoc->files()->create([
                    'name'      => $fileName,
                    'mime_type' => $file->getClientMimeType(),
                    'data'      => $fileData,
                ]);
            }
        }

        return redirect()
            ->route('hrd.importantDocs.detail', $importantDoc->id)
            ->with('success', 'Data berhasil diupdate!');
    }

    public function destroy($id)
    {
        $importantDoc = ImportantDoc::findOrFail($id);
        $importantDoc->delete();

        return redirect()
            ->route('hrd.importantDocs.index')
            ->with('success', 'Data berhasil dihapus!');
    }

    /**
     * Delete a single file attachment from a document.
     */
    public function destroyFile($fileId)
    {
        $file = ImportantDocFile::findOrFail($fileId);
        $file->delete();

        return back()->with('success', 'File berhasil dihapus!');
    }
}
