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
use App\Models\ProdplanInjDelitem;
use App\Models\ProdplanInjDelraw;
use App\Models\ProdplanInjDelsched;
use App\Models\ProdplanInjItem;
use Illuminate\Support\Facades\Date;

use App\DataTables\ProdplanInjDelschedDataTable;

class PPSInjectionController extends Controller
{
    public function indexscenario()
    {
        $data = ProdplanScenario::get();
        $datedata = UtiDateList::get();

        DB::table('prodplan_inj_delitems')->truncate();
			DB::table('prodplan_inj_delraws')->truncate();
			DB::table('prodplan_inj_delscheds')->truncate();		
			DB::table('prodplan_inj_items')->truncate();
			DB::table('prodplan_inj_linecaps')->truncate();
			DB::table('prodplan_inj_linelists')->truncate();
        
        // dd($data);
        return view("pps.injectionindex", compact("data", "datedata"));
    }


    public function processInjectionForm(Request $request)
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

        $datedata[14]->start_date = $request->input('start_date');
        $datedata[14]->end_date = $request->input('end_date');
        $data[0]->val_int_inj = $request->input('hm_fg');
        $data[1]->val_int_inj = $request->input('hm_wip');
        $data[2]->val_int_inj = $request->input('jarak_gudang');
        $data[3]->val_int_inj = $request->input('max_manpower');
        $data[4]->val_int_inj = $request->input('max_mould_change');
        $data[5]->val_int_inj = $request->input('forecast');
        $data[6]->val_int_inj = $request->input('count_wip');


        $datedata[14]->update(["start_date" => $request->input('start_date')]);
        $datedata[14]->update(["end_date" => $request->input('end_date')]);

        $data[0]->update(["val_int_inj" => $request->input('hm_fg')]);
        $data[1]->update(["val_int_inj" => $request->input('hm_wip')]);
        $data[2]->update(["val_int_inj" => $request->input('jarak_gudang')]);
        $data[3]->update(["val_int_inj" => $request->input('max_manpower')]);
        $data[4]->update(["val_int_inj" => $request->input('max_mould_change')]);
        $data[5]->update(["val_int_inj" => $request->input('forecast')]);
        $data[6]->update(["val_int_inj" => $request->input('count_wip')]);
        


        // Further processing or redirection goes here
        return redirect()->route('injectionprocess1');
    }

    public function process1()
    {
        DB::table('prodplan_inj_delraws')->truncate();
		DB::table('prodplan_inj_delscheds')->truncate();
		DB::table('prodplan_inj_delitems')->truncate();
		DB::table('prodplan_inj_items')->truncate();
		
		$tab_scenario_lead_fg = DB::table('prodplan_scenarios')->where('id',1)->first();
		$val_lead_fg = $tab_scenario_lead_fg->val_int_inj;
		
		$tab_scenario_lead_wip = DB::table('prodplan_scenarios')->where('id',2)->first();
		$val_lead_wip = $tab_scenario_lead_wip->val_int_inj;
		
		$tab_date_list = DB::table('uti_date_list')->where('id',15)->first();
		$val_start_date = $tab_date_list->start_date;
		$val_end_date = $tab_date_list->end_date;
		
		$val_past_date = (new Carbon($val_start_date))->addDays(-45);
		$val_advanced_date_fg = (new Carbon($val_end_date))->addDays($val_lead_fg);
		$val_advanced_date_wip = (new Carbon($val_end_date))->addDays($val_lead_wip);		
		
		$tab_delsched_final = DB::table('delsched_final')->where('outstanding_stk','>',0)->whereBetween('delivery_date',[$val_past_date , $val_advanced_date_fg])->get();
		
		foreach($tab_delsched_final as $delsched_final){
			
			$val_id_fg = $delsched_final->id;
			
			$tab_delsched_final_wip = DB::table('delsched_finalwip')->where('fglink_id',$val_id_fg)->first();
			
			if(empty($tab_delsched_final_wip->id)){
							
				$val_departement = $delsched_final->departement;
				
				$val_item_code = $delsched_final->item_code;
				$val_delivery_date = $delsched_final->delivery_date;
				$val_bom_level = 0;	
				$val_delivery_qty = $delsched_final->outstanding_stk;
				
				$tab_inventoryfg = DB::table('sap_inventory_fg')->where('item_code',$val_item_code)->first();
				$val_pair = $tab_inventoryfg->pair ?? 'Default';
				
				if(empty($val_item_code)){
				} else {
					$ins_delraw = array('delivery_date' => $val_delivery_date, 
					'bom_level' => $val_bom_level, 
					'item_code' => $val_item_code, 
					'item_pair' => $val_pair,
					'asm_on_line' => "", 
					'fg_code_line' => "", 
					'quantity' => $val_delivery_qty, 
					'process_owner' => $val_departement);
					ProdplanInjDelraw::insert($ins_delraw);
				}
				
			} else {
				
				$tab_delsched_final_wip_link = DB::table('delsched_finalwip')->where('fglink_id',$val_id_fg)->where('departement','390')->get();
				
				foreach($tab_delsched_final_wip_link as $wip_link){
					
					$val_departement = $wip_link->departement;
				
					$val_item_code = $wip_link->wip_code;
					$val_delivery_date = $wip_link->delivery_date;
					$val_bom_level = $wip_link->bom_level;	
					$val_delivery_qty = $wip_link->outstanding_wip;
					
					$tab_inventoryfg = DB::table('sap_inventory_fg')->where('item_code',$val_item_code)->first();
					$val_pair = $tab_inventoryfg->pair?? 'Default';
				}
				if($val_delivery_qty > 0){
					if(empty($val_item_code)){
					
					} else {
						$ins_delraw = array('delivery_date' => $val_delivery_date, 
						'bom_level' => $val_bom_level, 
						'item_code' => $val_item_code, 
						'item_pair' => $val_pair,
						'asm_on_line' => "", 
						'fg_code_line' => "", 
						'quantity' => $val_delivery_qty, 
						'process_owner' => $val_departement);
						ProdplanInjDelraw::insert($ins_delraw);
					}
				}
			}				
		}

		//Tarik ke delitem
		$tab_delraw_itemonly_paired = DB::table('prodplan_inj_delraws')->select('item_code','item_pair')->distinct()->get();
		
		foreach($tab_delraw_itemonly_paired as $delraw_itemonly_paired){
			
			$val_item_code = $delraw_itemonly_paired->item_code;
			$val_item_pair = $delraw_itemonly_paired->item_pair;
			
			if(empty($val_item_pair)){
			} else {
				$ins_delitem = array(	
					'item_code' => $val_item_code,
					'item_pair' => $val_item_pair);
				ProdplanInjDelitem::insert($ins_delitem);
			}
		}
		
		$tab_delraw_itemonly_nopair = DB::table('prodplan_inj_delraws')->select('item_code','item_pair')->distinct()->get();
		
		foreach($tab_delraw_itemonly_nopair as $delraw_itemonly_nopair){
			
			$val_item_code_ii = $delraw_itemonly_nopair->item_code;
			$val_item_pair_ii = $delraw_itemonly_nopair->item_pair;
			
			$tab_delitem = DB::table('prodplan_inj_delitems')->where('item_pair',$val_item_code_ii)->first();
			
			if(empty($tab_delitem->item_code)){
				if(empty($val_item_pair_ii)){
					$ins_delitem = array(
						'item_code' => $val_item_code_ii,
						'item_pair' => $val_item_pair_ii);
                        ProdplanInjDelitem::insert($ins_delitem);
				}
			}
		}

        return redirect()->route('injectionprocess2');

    }

    public function process2()
    {
        $tab_delraw_date = DB::table('prodplan_inj_delraws')->select('delivery_date')->orderBy('delivery_date','asc')->distinct()->get();
		
		foreach($tab_delraw_date as $delraw_date){
			
			$val_delivery_date = $delraw_date->delivery_date;
			
			$tab_delitem = DB::table('prodplan_inj_delitems')->get();
			
			foreach($tab_delitem as $delitem){
				
				$val_item_code = $delitem->item_code;
			
				$sum_del_qty = DB::table('prodplan_inj_delraws')->where('item_code',$val_item_code)->where('delivery_date',$val_delivery_date)->sum('quantity');							
				
				if($sum_del_qty>0){			
					
					if(empty($delitem->item_pair)){												
						
						$cal_final_qty = $sum_del_qty;
						
						$ins_delsched = array(	
							'item_code' => $val_item_code,
							'quantity' => $sum_del_qty,
							'actual_deldate' => $val_delivery_date,
							'final_quantity' => $cal_final_qty);
						ProdplanInjDelsched::insert($ins_delsched);
						
					} else {
					
						$val_pair_code = $delitem->item_pair;
						
						$sum_del_pair_qty = DB::table('prodplan_inj_delraws')->where('item_code',$val_pair_code)->where('delivery_date',$val_delivery_date)->sum('quantity');
					
						if($sum_del_pair_qty >= $sum_del_qty){
							$cal_final_qty = $sum_del_pair_qty;
						} else {							
							$cal_final_qty = $sum_del_qty;
						}
					
						$ins_delsched = array(								
							'item_code' => $val_item_code,
							'quantity' => $sum_del_qty,
							'pair_code' => $val_pair_code,
							'pair_quantity' => $sum_del_pair_qty,
							'actual_deldate' => $val_delivery_date,
							'final_quantity' => $cal_final_qty);
                            ProdplanInjDelsched::insert($ins_delsched);

					}
				} else {
					
					if(empty($delitem->item_pair)){
						
					} else {
						
						$val_pair_code = $delitem->item_pair;
						
						$sum_del_pair_qty = DB::table('prodplan_inj_delraws')->where('item_code',$val_pair_code)->where('delivery_date',$val_delivery_date)->sum('quantity');
						
						if($sum_del_pair_qty >= $sum_del_qty){
							$cal_final_qty = $sum_del_pair_qty;
						} else {							
							$cal_final_qty = $sum_del_qty;
						}
						
						if($sum_del_pair_qty>0){					
							$ins_delsched = array(	
								'item_code' => $val_item_code,
								'quantity' => $sum_del_qty,
								'pair_code' => $val_pair_code,
								'pair_quantity' => $sum_del_pair_qty,
								'actual_deldate' => $val_delivery_date,
								'final_quantity' => $cal_final_qty);
                                ProdplanInjDelsched::insert($ins_delsched);
						}
					}										
				}
				
			}
			
		}

        return redirect()->route('injectionprocess3');
    }

    public function process3()
    {

        $tab_delsched = DB::table('prodplan_inj_delscheds')->get();
		
		foreach($tab_delsched as $delsched){
			
			$val_delsched_id = $delsched->id;
			$val_item_code = $delsched->item_code;
			$tab_inventoryfg = DB::table('sap_inventory_fg')->where('item_code',$val_item_code)->first();
			
			$val_item_name = $tab_inventoryfg->item_name?? 'Default';
			$val_bom_level = $tab_inventoryfg->bom_level?? 'Default';
			
			if(empty($delsched->pair_code)){
				
				$val_pair_code_up = null;
				$val_pair_name = null;
				$val_pair_bom_level = null;
				$val_prior_item_code = $val_item_code;
				
			} else {
				
				$val_pair_code = $delsched->pair_code;
				$tab_inventoryfg_pair = DB::table('sap_inventory_fg')->where('item_code',$val_pair_code)->first();
				
				if(empty($tab_inventoryfg_pair->item_name)){
					$val_pair_code_up = null;				
					$val_pair_name = null;
					$val_pair_bom_level = null;
					$val_prior_item_code = $val_item_code;
					
				} else {
					
					$val_pair_code_up = $val_pair_code;	
					$val_pair_name = $tab_inventoryfg_pair->item_name;
					$val_pair_bom_level = $tab_inventoryfg_pair->bom_level;
					$val_prior_item_code = $val_pair_code;
					
				}
			}
			
			$tab_scen_lead_time_fg = DB::table('prodplan_scenarios')->where('id',1)->first();
			$val_lead_time_fg = $tab_scen_lead_time_fg->val_int_inj;
			
			$tab_scen_lead_time_wip = DB::table('prodplan_scenarios')->where('id',2)->first();
			$val_lead_time_wip = $tab_scen_lead_time_wip->val_int_inj;
			
			if(empty($val_pair_bom_level)){
				
				$val_deldate = $delsched->actual_deldate;
				
				if($val_bom_level < 1){
					
					$cal_lead_time_fg = -1*$val_lead_time_fg;
					$val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_fg);
					$val_prior_bom_level = $val_bom_level;
					$val_lead_time = $val_lead_time_fg;
					
				} else {
					
					$cal_lead_time_wip = -1*$val_lead_time_wip;
					$val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_wip);
					$val_prior_bom_level = $val_bom_level;
					$val_lead_time = $val_lead_time_wip;
					
				}
				
			} else {
				
				if($val_bom_level > $val_pair_bom_level){
					
					if($val_bom_level < 1){
					
					$cal_lead_time_fg = -1*$val_lead_time_fg;
					$val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_fg);
					$val_prior_bom_level = $val_bom_level;
					$val_lead_time = $val_lead_time_fg;
					
					} else {
						
						$cal_lead_time_wip = -1*$val_lead_time_wip;
						$val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_wip);
						$val_prior_bom_level = $val_bom_level;
						$val_lead_time = $val_lead_time_wip;
						
					}
					
				} else {
					
					if($val_pair_bom_level < 1){
					
					$cal_lead_time_fg = -1*$val_lead_time_fg;
					$val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_fg);
					$val_prior_bom_level = $val_pair_bom_level;
					$val_lead_time = $val_lead_time_fg;
					
					} else {
						
						$cal_lead_time_wip = -1*$val_lead_time_wip;
						$val_new_deldate = (new Carbon($val_deldate))->addDays($cal_lead_time_wip);
						$val_prior_bom_level = $val_pair_bom_level;
						$val_lead_time = $val_lead_time_wip;
						
					}
					
				}
				
			}			
			
			$now = Carbon::now();
			
			if($val_new_deldate < $now){
				if($val_deldate < $now){
					$val_color = 'danger';
				} else {
					$val_color = 'warning';
				}
			} else {				
				$val_color = 'light';				
			}				
			
			//Update data di tabel delsched
			DB::table('prodplan_inj_delscheds')->where('id',$val_delsched_id)->update([	
				'delivery_date' => $val_new_deldate,
				'item_name' => $val_item_name,
				'item_bom_level' => $val_bom_level,
				'pair_code' => $val_pair_code_up,
				'pair_name' => $val_pair_name,
				'pair_bom_level' => $val_pair_bom_level,
				'prior_item_code' => $val_prior_item_code,
				'prior_bom_level' => $val_prior_bom_level,
				'completed' => 0,
				'outstanding' => $delsched->final_quantity,
				'status' => 0,
				'remarks' => 'Not Completed',
				'remarks_leadtime' => $val_lead_time,
				'color' => $val_color,
			]);
			
		}

        return redirect()->route('deliveryinjection');

    }







    public function deliveryinjection(ProdplanInjDelschedDataTable $dataTable)
    {
        // $tab_delsched = ProdplanInjDelsched::get();
        // dd($tab_delsched);
        // return view("pps.injectiondelivery");
        return $dataTable->render("pps.injectiondelivery");
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
