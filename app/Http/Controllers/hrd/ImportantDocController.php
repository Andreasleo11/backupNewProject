<?php

namespace App\Http\Controllers\hrd;

use Illuminate\Http\Request;
use App\Models\hrd\ImportantDoc;
use App\Models\hrd\ImportantDocType;
use App\Http\Controllers\Controller;

class ImportantDocController extends Controller
{
    public function index()
    {
        $important_docs = ImportantDoc::with('type')->orderBy('expired_date')->get();
        return view('hrd.importantDocs.index', compact('important_docs'));
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
     * Store a new important_doc in the database.
     */
    public function store(Request $request)
    {
        // Validate form
        $this->validate($request, [
            'name'=>'required|max:255',
            'type_id'=>'required',
            'expired_date'=>'required',
        ]);

        ImportantDoc::create([
            'name'     => $request->name,
            'type_id'     => $request->type_id,
            'expired_date'   => $request->expired_date
        ]);

        return redirect()->route('hrd.importantDocs')->with('success', 'Data berhasil dibuat!');
    }

    public function detail($id)
    {
        $importantDoc = ImportantDoc::find($id);
        return view('hrd.importantDocs.detail', compact('importantDoc'));
    }

    public function edit($id)
    {
        $types = ImportantDocType::all()->reverse();
        $importantDoc = ImportantDoc::with('type')->find($id);
        return view('hrd.importantDocs.edit', compact('importantDoc', 'types'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            "name" => 'required|max:255',
            "type_id" => 'required|max:255',
            "expired_date" => 'required',
        ]);

        $importantDoc = ImportantDoc::find($id);

        if ($importantDoc) {
            $importantDoc->update($request->all());
            return redirect()->route('hrd.importantDocs')->with('success', 'Data berhasil diupdate!');
        } else {
            return redirect()->route('hrd.importantDocs')->with('error', 'Data not found!');
        }
    }

    public function destroy($id)
    {
        $importantDoc = ImportantDoc::find($id)->delete();
        return redirect()->route('hrd.importantDocs')->with('success', 'Data berhasil dihapus!');
    }

}
