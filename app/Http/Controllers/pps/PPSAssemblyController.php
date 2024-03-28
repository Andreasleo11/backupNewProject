<?php

namespace App\Http\Controllers\pps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProdplanScenario;
use App\Models\UtiDateList;
use Illuminate\Support\Facades\Date;

class PPSAssemblyController extends Controller
{
    public function indexscenario()
    {
        $data = ProdplanScenario::get();
        $datedata = UtiDateList::get();

        DB::table('prodplan_asm_delitems')->truncate();
        DB::table('prodplan_asm_delraws')->truncate();
        DB::table('prodplan_asm_delscheds')->truncate();		
        DB::table('prodplan_asm_items')->truncate();
        DB::table('prodplan_asm_linecaps')->truncate();
        DB::table('prodplan_asm_linelists')->truncate();
        return view("pps.assemblyindex", compact("data", "datedata"));
    }


    public function processAssemblyForm(Request $request)
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
  
          $datedata[16]->start_date = $request->input('start_date');
          $datedata[16]->end_date = $request->input('end_date');
          $data[0]->val_int_asm = $request->input('hm_fg');
          $data[1]->val_int_asm = $request->input('hm_wip');
          $data[2]->val_int_asm = $request->input('jarak_gudang');
          $data[3]->val_int_asm = $request->input('max_manpower');
          $data[4]->val_int_asm = $request->input('max_mould_change');
          $data[5]->val_int_asm = $request->input('forecast');
          $data[6]->val_int_asm = $request->input('count_wip');
  
  
          $datedata[16]->update(["start_date" => $request->input('start_date')]);
          $datedata[16]->update(["end_date" => $request->input('end_date')]);
  
          $data[0]->update(["val_int_asm" => $request->input('hm_fg')]);
          $data[1]->update(["val_int_asm" => $request->input('hm_wip')]);
          $data[2]->update(["val_int_asm" => $request->input('jarak_gudang')]);
          $data[3]->update(["val_int_asm" => $request->input('max_manpower')]);
          $data[4]->update(["val_int_asm" => $request->input('max_mould_change')]);
          $data[5]->update(["val_int_asm" => $request->input('forecast')]);
          $data[6]->update(["val_int_asm" => $request->input('count_wip')]);
          
  
  
          // Further processing or redirection goes here
          return redirect()->route('deliveryassembly');
    }

    public function deliveryassembly()
    {
        return view("pps.assemblydelivery");
    }

    public function itemassembly()
    {
        return view("pps.assemblyitem");
    }

    public function lineassembly()
    {
        return view("pps.assemblyline");
    }

    public function finalresultassembly()
    {
        return view("pps.finalresultppsassembly");
    }
}
