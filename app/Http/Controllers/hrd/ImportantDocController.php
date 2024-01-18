<?php

namespace App\Http\Controllers\hrd;

use Illuminate\Http\Request;
use App\Models\hrd\ImportantDoc;
use App\Http\Controllers\Controller;

class ImportantDocController extends Controller
{
    public function index()
    {
        $important_doc = ImportantDoc::get();
        return view('hrd.important_docs', compact('important_doc'));
    }

    /**
     * Store a new important_doc in the database.
     */
    public function store(Request $request)
    {
        $important_doc = new ImportantDoc;

        $important_doc->id = $request->id;
        $important_doc->name = $request->name;
        $important_doc->type = $request->type;
        $important_doc->expired_date = $request->expired_date;

        // $id = $request->input('id');
        // $name = $request->input('name');
        // $type = $request->input('type');
        // $expired_date = $request->input('expired_date');

        $important_doc->save();

        return redirect()->route('hrd.important_docs');

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
