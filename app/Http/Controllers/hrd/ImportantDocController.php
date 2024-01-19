<?php

namespace App\Http\Controllers\hrd;

use Illuminate\Http\Request;
use App\Models\hrd\ImportantDoc;
use App\Http\Controllers\Controller;

class ImportantDocController extends Controller
{
    public function index()
    {
        $important_docs = ImportantDoc::get();
        return view('hrd.important_docs', compact('important_docs'));
    }

    /**
     * Return a create view
     */

    public function create()
    {
        return view('hrd.important_docs_create');
    }

    /**
     * Store a new important_doc in the database.
     */
    public function store(Request $request)
    {
        // Validate form
        $this->validate($request, [
            'name'=>'required|max:255',
            'type'=>'required|max:255',
            'expired_date'=>'required',
        ]);

        ImportantDoc::create([
            'name'     => $request->name,
            'type'     => $request->type,
            'expired_date'   => $request->expired_date
        ]);

        return redirect()->route('hrd.importantDocs', with(['success' => 'Data berhasil disimpan']));
    }


    // public function store(Request $request): RedirectResponse
    // {
    //     // Validate the request...

    //     $flight = new Flight;

    //     $flight->name = $request->name;

    //     $flight->save();

    //     return redirect('/flights');
    // }


}
