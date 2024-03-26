<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use DateTime;
use Carbon\Carbon;

use App\Models\prodplan_scenario;
use App\Models\prodplan_line_repair;

use App\Models\prodplan_inj_linecap;
use App\Models\prodplan_inj_linelist;
use App\Models\prodplan_inj_delraw;
use App\Models\prodplan_inj_delsched;
use App\Models\prodplan_inj_delitem;
use App\Models\prodplan_inj_items;

class NewProdController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
	
	public function pps_wizard()
    {				
        return view('program/new_production/pps_wizard/pps_wizard');
    }
	
	public function pps_wizard_step_0_dept()
    {		
		$tab_date_list_inj = DB::table('uti_date_list')->where('id',15)->first();
		$val_last_updated_inj = $tab_date_list_inj->last_update;
		
		$tab_date_list_snd = DB::table('uti_date_list')->where('id',2)->first();
		$val_last_updated_snd = $tab_date_list_snd->last_update;
		
		$tab_date_list_asm = DB::table('uti_date_list')->where('id',3)->first();
		$val_last_updated_asm = $tab_date_list_asm->last_update;
	
        return view('program/new_production/pps_wizard/pps_wizard_step_0_dept',['date_inj' => $val_last_updated_inj, 'date_snd' => $val_last_updated_snd, 'date_asm' => $val_last_updated_asm]);
    }
	
	public function pps_wizard_step_0_dept_process(Request $request){
		
		if($request->scenario == "INJ"){
				
			DB::table('prodplan_inj_delitem')->truncate();
			DB::table('prodplan_inj_delraw')->truncate();
			DB::table('prodplan_inj_delsched')->truncate();		
			DB::table('prodplan_inj_items')->truncate();
			DB::table('prodplan_inj_linecap')->truncate();
			DB::table('prodplan_inj_linelist')->truncate();
			
			return Redirect::action('NewProdController@pps_wizard_inj_step_1_scenario');
			
		} elseif($request->scenario == "SND"){	
		
			return Redirect::action('NewProdController@pps_wizard_snd_step_1_scenario');
			
		} else {		
		
			return Redirect::action('NewProdController@pps_wizard_asm_step_1_scenario');
			
		}
		
	}
	
	public function pps_wizard_inj_step_1_scenario(){				
		
		$tab_scenario_lead_time_fg = DB::table('prodplan_scenario')->where('id',1)->first();
		$tab_scenario_lead_time_wip = DB::table('prodplan_scenario')->where('id',2)->first();
		$tab_scenario_day_saving = DB::table('prodplan_scenario')->where('id',3)->first();
		$tab_scenario_man_power = DB::table('prodplan_scenario')->where('id',4)->first();
		$tab_scenario_mould_change = DB::table('prodplan_scenario')->where('id',5)->first();
		$tab_scenario_run_forecast = DB::table('prodplan_scenario')->where('id',6)->first();
		
		$tab_date_list_inj = DB::table('uti_date_list')->where('id',15)->first();
		$val_start_date_inj = $tab_date_list_inj->start_date;
		$val_end_date_inj = $tab_date_list_inj->end_date;
		
		$val_lead_time_fg = $tab_scenario_lead_time_fg->val_int_inj;
		$val_lead_time_wip = $tab_scenario_lead_time_wip->val_int_inj;
		$val_day_saving = $tab_scenario_day_saving->val_int_inj;
		$val_man_power = $tab_scenario_man_power->val_int_inj;
		$val_mould_change = $tab_scenario_mould_change->val_int_inj;
		$val_run_forecast = $tab_scenario_run_forecast->val_int_inj;
		
		return view('program/new_production/pps_wizard/injection/pps_wizard_injection_step_1_scenario',['start_date' => $val_start_date_inj, 'end_date' => $val_end_date_inj,
		'lead_time_fg' => $val_lead_time_fg, 'lead_time_wip' => $val_lead_time_wip, 'day_saving' => $val_day_saving, 'man_power' => $val_man_power,
		'mould_change' => $val_mould_change, 'run_forecast' => $val_run_forecast]);
	}
	
	public function pps_wizard_inj_step_1_scenario_process(Request $request){				
		
		$update_date = DB::table('uti_date_list')->where('id', 15)->update([
			'start_date' => $request->start_date,	
			'end_date' => $request->end_date,
		]);
		
		$update_lead_time_fg = DB::table('prodplan_scenario')->where('id', 1)->update([					
			'val_int_inj' => $request->lead_time_fg,				
		]);
		
		$update_lead_time_wip = DB::table('prodplan_scenario')->where('id', 2)->update([					
			'val_int_inj' => $request->lead_time_wip,				
		]);
		
		$update_day_saving = DB::table('prodplan_scenario')->where('id', 3)->update([					
			'val_int_inj' => $request->day_saving,				
		]);
		
		$update_man_power = DB::table('prodplan_scenario')->where('id', 4)->update([					
			'val_int_inj' => $request->man_power,				
		]);
		
		$update_mould_change = DB::table('prodplan_scenario')->where('id', 5)->update([					
			'val_int_inj' => $request->mould_change,				
		]);
		
		if($request->run_forecast == 'Y'){
			$val_int_inj = 1;
		} else {
			$val_int_inj = 0;
		}
		
		$update_run_forecast = DB::table('prodplan_scenario')->where('id', 6)->update([					
			'val_int_inj' => $val_int_inj,	
			'val_vc_inj' => $request->run_forecast,
		]);
		
		return Redirect::action('NewProdController@pps_wizard_inj_step_2_delsched_process');
	}
	
	public function pps_wizard_inj_step_2_delsched_process(){				
		
		DB::table('prodplan_inj_delraw')->truncate();
		DB::table('prodplan_inj_delsched')->truncate();
		DB::table('prodplan_inj_delitem')->truncate();
		DB::table('prodplan_inj_items')->truncate();
		
		$tab_scenario_lead_fg = DB::table('prodplan_scenario')->where('id',1)->first();
		$val_lead_fg = $tab_scenario_lead_fg->val_int_inj;
		
		$tab_scenario_lead_wip = DB::table('prodplan_scenario')->where('id',2)->first();
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
					prodplan_inj_delraw::insert($ins_delraw);
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
						prodplan_inj_delraw::insert($ins_delraw);
					}
				}
			}				
		}

		//Tarik ke delitem
		$tab_delraw_itemonly_paired = DB::table('prodplan_inj_delraw')->select('item_code','item_pair')->distinct()->get();
		
		foreach($tab_delraw_itemonly_paired as $delraw_itemonly_paired){
			
			$val_item_code = $delraw_itemonly_paired->item_code;
			$val_item_pair = $delraw_itemonly_paired->item_pair;
			
			if(empty($val_item_pair)){
			} else {
				$ins_delitem = array(	
					'item_code' => $val_item_code,
					'item_pair' => $val_item_pair);
				prodplan_inj_delitem::insert($ins_delitem);
			}
		}
		
		$tab_delraw_itemonly_nopair = DB::table('prodplan_inj_delraw')->select('item_code','item_pair')->distinct()->get();
		
		foreach($tab_delraw_itemonly_nopair as $delraw_itemonly_nopair){
			
			$val_item_code_ii = $delraw_itemonly_nopair->item_code;
			$val_item_pair_ii = $delraw_itemonly_nopair->item_pair;
			
			$tab_delitem = DB::table('prodplan_inj_delitem')->where('item_pair',$val_item_code_ii)->first();
			
			if(empty($tab_delitem->item_code)){
				if(empty($val_item_pair_ii)){
					$ins_delitem = array(
						'item_code' => $val_item_code_ii,
						'item_pair' => $val_item_pair_ii);
					prodplan_inj_delitem::insert($ins_delitem);
				}
			}
		}
		
		return Redirect::action('NewProdController@pps_wizard_inj_step_2_delsched_process_ii');
	}
	
	public function pps_wizard_inj_step_2_delsched_process_ii(){				
		
		$tab_delraw_date = DB::table('prodplan_inj_delraw')->select('delivery_date')->orderBy('delivery_date','asc')->distinct()->get();
		
		foreach($tab_delraw_date as $delraw_date){
			
			$val_delivery_date = $delraw_date->delivery_date;
			
			$tab_delitem = DB::table('prodplan_inj_delitem')->get();
			
			foreach($tab_delitem as $delitem){
				
				$val_item_code = $delitem->item_code;
			
				$sum_del_qty = DB::table('prodplan_inj_delraw')->where('item_code',$val_item_code)->where('delivery_date',$val_delivery_date)->sum('quantity');							
				
				if($sum_del_qty>0){			
					
					if(empty($delitem->item_pair)){												
						
						$cal_final_qty = $sum_del_qty;
						
						$ins_delsched = array(	
							'item_code' => $val_item_code,
							'quantity' => $sum_del_qty,
							'actual_deldate' => $val_delivery_date,
							'final_quantity' => $cal_final_qty);
						prodplan_inj_delsched::insert($ins_delsched);
						
					} else {
					
						$val_pair_code = $delitem->item_pair;
						
						$sum_del_pair_qty = DB::table('prodplan_inj_delraw')->where('item_code',$val_pair_code)->where('delivery_date',$val_delivery_date)->sum('quantity');
					
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
						prodplan_inj_delsched::insert($ins_delsched);

					}
				} else {
					
					if(empty($delitem->item_pair)){
						
					} else {
						
						$val_pair_code = $delitem->item_pair;
						
						$sum_del_pair_qty = DB::table('prodplan_inj_delraw')->where('item_code',$val_pair_code)->where('delivery_date',$val_delivery_date)->sum('quantity');
						
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
							prodplan_inj_delsched::insert($ins_delsched);
						}
					}										
				}
				
			}
			
		}
		
		return Redirect::action('NewProdController@pps_wizard_inj_step_2_delsched_process_iii');
	}
	
	public function pps_wizard_inj_step_2_delsched_process_iii(){				
		
		$tab_delsched = DB::table('prodplan_inj_delsched')->get();
		
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
			
			$tab_scen_lead_time_fg = DB::table('prodplan_scenario')->where('id',1)->first();
			$val_lead_time_fg = $tab_scen_lead_time_fg->val_int_inj;
			
			$tab_scen_lead_time_wip = DB::table('prodplan_scenario')->where('id',2)->first();
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
			DB::table('prodplan_inj_delsched')->where('id',$val_delsched_id)->update([	
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
		
		return Redirect::action('NewProdController@pps_wizard_inj_step_2_delsched');
	}
	
	public function pps_wizard_inj_step_2_delsched(){				
		
		$tab_delsched = DB::table('prodplan_inj_delsched')->get();
		
		return view('program/new_production/pps_wizard/injection/pps_wizard_injection_step_2_delsched',['delsched' => $tab_delsched]);
	}
	
	public function pps_wizard_inj_step_3_items_process(){
		
		DB::table('prodplan_inj_items')->truncate();
		
		$tab_delsched_itemonly = DB::table('prodplan_inj_delsched')->select('item_code')->distinct()->get();
		
		foreach($tab_delsched_itemonly as $delsched_itemonly){
						
			$ins_items = array(					
				'item_code' => $delsched_itemonly->item_code);
			prodplan_inj_items::insert($ins_items);
			
		}
		
		$tab_items = DB::table('prodplan_inj_items')->get();
		
		foreach($tab_items as $items){
			
			$val_items_id = $items->id;
			$val_item_code = $items->item_code;
			$tab_delsched = DB::table('prodplan_inj_delsched')->where('item_code',$val_item_code)->first();
			$val_pair_code = $tab_delsched->pair_code;
			$val_bom_level = $tab_delsched->prior_bom_level;
			$val_lead_time = $tab_delsched->remarks_leadtime;
			
			$sum_outstanding = DB::table('prodplan_inj_delsched')->where('item_code',$val_item_code)->sum('outstanding');
			
			//Update data di tabel items
			DB::table('prodplan_inj_items')->where('id',$val_items_id)->update([			
				'pair_code' => $val_pair_code,
				'bom_level' => $val_bom_level,
				'lead_time' => $val_lead_time,
				'total_delivery' => $sum_outstanding,
			]);
		}
		
		$tab_items_up = DB::table('prodplan_inj_items')->get();
		
		foreach($tab_items as $items){
			
			$val_items_id = $items->id;
			
			if(empty($items->pair_code)){
				$val_prior_item = $items->item_code;				
			} else {
				$val_prior_item = $items->pair_code;
			}
			
			$tab_inventory_fg = DB::table('sap_inventory_fg')->where('item_code',$val_prior_item)->first();
			
			$val_continue_prod = $tab_inventory_fg->continue_production;
			$val_cycle_time_raw = $tab_inventory_fg->cycle_time;
			$val_daily_limit = $tab_inventory_fg->daily_limit;
			$val_prod_min = $tab_inventory_fg->production_min_qty;
			$val_cavity = $tab_inventory_fg->cavity;
			$val_safety_stock = $tab_inventory_fg->safety_stock;
			
			//Update data di tabel items
			DB::table('prodplan_inj_items')->where('id',$val_items_id)->update([							
				'continue_prod' => $val_continue_prod,
				'safety_stock' => $val_safety_stock,
				'daily_limit' => $val_daily_limit,
				'prod_min' => $val_prod_min,
				'cycle_time_raw' => $val_cycle_time_raw,
				'cavity' => $val_cavity,
			]);
		}	
		
		return Redirect::action('NewProdController@pps_wizard_inj_step_3_items');
	}
	
	public function pps_wizard_inj_step_3_items(){				
		
		$tab_items = DB::table('prodplan_inj_items')->get();
		
		return view('program/new_production/pps_wizard/injection/pps_wizard_injection_step_3_items',['items' => $tab_items]);
	}
}
