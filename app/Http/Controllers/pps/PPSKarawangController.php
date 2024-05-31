<?php

namespace App\Http\Controllers\pps;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use DateTime;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProdplanScenario;
use App\Models\UtiDateList;


class PPSKarawangController extends Controller
{
    public function index()
    {

        $data = ProdplanScenario::get();
        $datedata = UtiDateList::get();

        DB::table('prodplan_inj_delitems')->truncate();
			DB::table('prodplan_inj_delraws')->truncate();
			DB::table('prodplan_inj_delscheds')->truncate();		
			DB::table('prodplan_inj_items')->truncate();
			DB::table('prodplan_inj_linecaps')->truncate();
			DB::table('prodplan_inj_linelists')->truncate();


        return view("pps.karawangindex", compact("data", 'datedata'));
    }

    public function processKarawangForm(Request $request)
    {
        // Validate the form data
        $data = ProdplanScenario::get();
        $datedata = UtiDateList::get();
        // dd($request->all());
        $validatedData = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'hm_fg' => 'required|integer',
            'hm_wip' => 'required|integer',
            'jarak_gudang' => 'required|integer',
            'max_manpower' => 'required|integer',
            'max_mould_change' => 'required|integer',
            'forecast' => 'required',
            'count_wip' => 'required',
        ]);

        // Process the form data
        // You can access the form data using $request->input('field_name')
        // For example:

        $datedata[17]->start_date = $request->input('start_date');
        $datedata[17]->end_date = $request->input('end_date');
        $data[0]->val_int_kri = $request->input('hm_fg');
        $data[1]->val_int_kri = $request->input('hm_wip');
        $data[2]->val_int_kri = $request->input('jarak_gudang');
        $data[3]->val_int_kri = $request->input('max_manpower');
        $data[4]->val_int_kri = $request->input('max_mould_change');
        $data[5]->val_int_kri = $request->input('forecast');
        $data[6]->val_int_kri = $request->input('count_wip');


        $datedata[17]->update(["start_date" => $request->input('start_date')]);
        $datedata[17]->update(["end_date" => $request->input('end_date')]);

        $data[0]->update(["val_int_kri" => $request->input('hm_fg')]);
        $data[1]->update(["val_int_kri" => $request->input('hm_wip')]);
        $data[2]->update(["val_int_kri" => $request->input('jarak_gudang')]);
        $data[3]->update(["val_int_kri" => $request->input('max_manpower')]);
        $data[4]->update(["val_int_kri" => $request->input('max_mould_change')]);
        $data[5]->update(["val_int_kri" => $request->input('forecast')]);
        $data[6]->update(["val_int_kri" => $request->input('count_wip')]);
        


        // Further processing or redirection goes here
        return redirect()->route('indexkarawang');
    }

    
}