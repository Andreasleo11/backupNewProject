<?php

namespace App\Http\Controllers\pps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PPSInjectionController extends Controller
{
    public function indexscenario()
    {
        return view("pps.injectionindex");
    }


    public function processInjectionForm(Request $request)
    {
        // Validate the form data
        // dd($request->all());
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'hm_fg' => 'required|integer',
            'hm_wip' => 'required|integer',
            'jarak_gudang' => 'required|integer',
            'max_manpower' => 'required|integer',
            'max_mould_change' => 'required|integer',
            'forecast' => 'required|in:ya,tidak',
        ]);

        // Process the form data
        // You can access the form data using $request->input('field_name')
        // For example:
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $hmFg = $request->input('hm_fg');
        $hmWip = $request->input('hm_wip');
        $jarakGudang = $request->input('jarak_gudang');
        $maxManpower = $request->input('max_manpower');
        $maxMouldChange = $request->input('max_mould_change');
        $forecast = $request->input('forecast');

        // Further processing or redirection goes here
        return redirect()->route('deliveryinjection');
    }


    public function deliveryinjection()
    {
        return view("pps.injectiondelivery");
    }

    public function iteminjection()
    {
        return view("pps.injectionitem");
    }

    public function lineinjection()
    {
        return view("pps.injectionline");
    }

    public function finalresultinjection()
    {
        return view("pps.finalresultppsinjection");
    }


}
