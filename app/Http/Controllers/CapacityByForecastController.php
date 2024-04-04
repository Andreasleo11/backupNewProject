<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use DateTime;
use Carbon\Carbon;

use Illuminate\Http\Request;
use App\Models\CapLineSummary;
use App\Models\CapItem;
use App\Models\CapLineCapacity;
use App\Models\CapLineDistribution;
use App\Models\CapForecast;
use App\Models\CapForecastDiv;
use App\Models\CapForecastFinal;
use App\Models\CapForecastSorted;
use App\Models\CapForecastSum;
use App\Models\InvLineList;
use App\Models\UtiDateList;

use App\DataTables\CapLineCapacityDataTable;
use App\DataTables\CapItemDataTable;
use App\DataTables\CapLineSummaryDataTable;
use App\DataTables\CapLineDistributionDataTable;


class CapacityByForecastController extends Controller
{
    public function index(CapLineSummaryDataTable $dataTable)
    {
		
        // $data = CapLineSummary::get();
        // $time = UtiDateList::find(8);
        // $startdate = $time->start_date;        

        // return view("production.capacity_forecast.index", compact( "data","startdate"));
        return $dataTable->render("production.capacity_forecast.index");
    }

    public function line(CapLineCapacityDataTable $dataTable)
    {
        return $dataTable->render("production.capacity_forecast.line");
    }

    public function distribution(CapLineDistributionDataTable $dataTable)
    {
        return $dataTable->render("production.capacity_forecast.distribution");
    }

    public function detail(CapItemDataTable $dataTable)
    {
        return $dataTable->render("production.capacity_forecast.detail");
    }



    public function viewstep1()
    {
        return view("production.capacity_forecast.step1");
    }

    public function step1(Request $request)
    {
        
        DB::table('cap_forecasts')->truncate();
		DB::table('cap_forecast_divs')->truncate();
		DB::table('cap_forecast_finals')->truncate();
		DB::table('cap_forecast_sorteds')->truncate();
		DB::table('cap_forecast_sums')->truncate();
		DB::table('cap_items')->truncate();
		DB::table('cap_line_summaries')->truncate();				
		
		$val_start_date = $request->start_date;			
		$fmonthonly = (new Carbon($val_start_date))->month;
		$val_full_day = Carbon::now()->month($fmonthonly)->daysInMonth;
		$val_full_day_min = $val_full_day-1;
		$val_end_date = (new Carbon($val_start_date))->addDays($val_full_day_min);
		
		$cnt_holiday_halfday = DB::table('uti_holiday_list')->whereBetween('date',[$val_start_date , $val_end_date])->where('half_day',1)->count();
		
		$cnt_holiday_fullday = DB::table('uti_holiday_list')->whereBetween('date',[$val_start_date , $val_end_date])->where('half_day',0)->count();
		
		$cal_work_day = $val_full_day - ($cnt_holiday_fullday) - ($cnt_holiday_halfday/2);
		
		DB::table('uti_date_list')->where('id',8)->update([
			'start_date' => $request->start_date,
			'end_date' => $val_end_date,
			'date_interval' => $val_full_day,
			'additional_value_dec' => $cal_work_day,
        ]);
		
		//Penarikan data forecast
		$tab_forecast = DB::table('sap_forecast')->where('forecast_date',$val_start_date)->get();
		
		foreach($tab_forecast as $fore_forecast){					
			$val_item_no = $fore_forecast->item_no;					
			$val_quantity = $fore_forecast->quantity;
			$val_quantity_next = 0;
			$cal_total = $val_quantity + $val_quantity_next;
						
			$ins_forecast = array('item_code' => $val_item_no, 'quantity' => $val_quantity, 'quantity_next' => $val_quantity_next, 'total' => $cal_total);	
			CapForecast::insert($ins_forecast);												
		}
		
		//Pembagian Part forecast		
		
		//Pembagian Part berdasarkan delivery schedule
		$tab_forecast_aft = DB::table('cap_forecasts')->get();
		
		foreach($tab_forecast_aft as $fore_forecast_aft){
			$val_item_code = $fore_forecast_aft->item_code;	
			$val_fct_qty = $fore_forecast_aft->total;
			
			$ins_forecast_div = array('father_part' => 'None', 'item_code' => $val_item_code, 'quantity' => $val_fct_qty, 'level' => 0);		
			CapForecastDiv::insert($ins_forecast_div);
			
			$tab_bom_wip = DB::table('sap_fct_bom_wip')->where('fg_code',$val_item_code)->get();
						
			foreach($tab_bom_wip as $fore_bom_wip){
				$val_level = $fore_bom_wip->level;
				
				if($val_level == 1){
					
					$val_bom_qty = $fore_bom_wip->bom_quantity;
					
					$cal_fct_qty = $val_bom_qty * $val_fct_qty;
					
					$val_wip_code = $fore_bom_wip->semi_first;
					
					$ins_forecast_div = array('father_part' => $val_item_code, 'item_code' => $val_wip_code,  'quantity' => $cal_fct_qty, 'level' => 1);		
					CapForecastDiv::insert($ins_forecast_div);
					
				} else if ($val_level == 2){
					
					$val_bom_qty = $fore_bom_wip->bom_quantity;
					
					$cal_fct_qty = $val_bom_qty * $val_fct_qty;
					
					$val_wip_code = $fore_bom_wip->semi_second;
					
					$ins_forecast_div = array('father_part' => $val_item_code, 'item_code' => $val_wip_code,  'quantity' => $cal_fct_qty, 'level' => 2);		
					CapForecastDiv::insert($ins_forecast_div);
					
				} else if ($val_level == 3){
					
					$val_bom_qty = $fore_bom_wip->bom_quantity;
					
					$cal_fct_qty = $val_bom_qty * $val_fct_qty;
					
					$val_wip_code = $fore_bom_wip->semi_third;
					
					$ins_forecast_div = array('father_part' => $val_item_code, 'item_code' => $val_wip_code,  'quantity' => $cal_fct_qty, 'level' => 3);		
					CapForecastDiv::insert($ins_forecast_div);
				}
			}							
		}		
		
		return redirect()->route("step1second");


        
    }

    public function step1_second(Request $request)
    {
        DB::table('cap_line_distributions')->truncate();

		
		
		// $test1= DB::table('cap_forecast_sorteds')->select('item_code')->distinct()->get();
		// foreach($test1 as $test2){
		// 	$code = $test2->item_code;
		// 	$test = DB::table('sap_fct_inventory_fgs')->where('item_code',$code)->first();
		// 	// foreach ($test as $row) {
		// 	// 	// Access and print the cycle_time property of each row
		// 	// 	$ano[] = $row->cycle_time;
		// 	// }
		// }
		// dd($test1);
	
		$tab_forecast_div = DB::table('cap_forecast_divs')->get();
	
		foreach($tab_forecast_div as $fore_forecast_div){			
			$val_forecast_qty = $fore_forecast_div->quantity;
			$val_item_code = $fore_forecast_div->item_code;
			$tab_fct_inventory_fg = DB::table('sap_fct_inventory_fgs')->where('item_code',$val_item_code)->first();		
			
			if(empty($tab_fct_inventory_fg->pair)){
				if(empty($tab_fct_inventory_fg->fg_code_1)){															
					
					$ins_forecast_sorted = array('item_code' => $val_item_code, 'quantity' => $val_forecast_qty);		
					CapForecastSorted::insert($ins_forecast_sorted);
				}
			}
		}		
		
		$tab_forecast_sorted = DB::table('cap_forecast_sorteds')->select('item_code')->distinct()->get();
		foreach($tab_forecast_sorted as $fore_forecast_sorted){						
			$val_item_code_srt = $fore_forecast_sorted->item_code;
							
			$tab_sap_lineproduction = DB::table('sap_fct_lineproductions')->where('item_code',$val_item_code_srt)->orderBy('priority','asc')->get();
			
			foreach($tab_sap_lineproduction as $fore_sap_line_production){			
				$val_item_code_line = $fore_sap_line_production->item_code;
				
				$tab_forecast_sorted_item = DB::table('cap_forecast_sorteds')->where('item_code',$val_item_code_line)->first();
				
				if(empty($tab_forecast_sorted_item->item_code)){
					
				} else {
					
					$tab_forecast_div = DB::table('cap_forecast_divs')->where('item_code',$val_item_code_line)->orderBy('level','desc')->first();
					$val_level = $tab_forecast_div->level;
					
					$val_line_code = $fore_sap_line_production->line_production;
					$val_priority = $fore_sap_line_production->priority;					
										
					$ins_line_distribution = array('bom_level' => $val_level, 'line_code' => $val_line_code, 'item_code' => $val_item_code_line, 'priority' => $val_priority);		
					CapLineDistribution::insert($ins_line_distribution);
					
				}
			}
						
			$tab_fct_inventory_fg = DB::table('sap_fct_inventory_fgs')->where('item_code',$val_item_code_srt)->first();	
		

			$val_cycle_time_raw = $tab_fct_inventory_fg->cycle_time;
			
			$val_process_owner = $tab_fct_inventory_fg->process_owner;
			
			$tab_fct_inventory_fg_pair = DB::table('sap_fct_inventory_fgs')->where('pair',$val_item_code_srt)->first();
			if(empty($tab_fct_inventory_fg_pair->item_code)){
				$val_pair = null;
			} else {
				$val_pair = $tab_fct_inventory_fg_pair->item_code;
			}
			
			if($val_process_owner == "INJ"){
				$val_departement = 390;
			} elseif($val_process_owner == "SEC"){
				$val_departement = 361;
			} else {
				$val_departement = 362;
			}
			
			if($val_departement == 390){
				
				if($tab_fct_inventory_fg->cavity == 0){
					$val_cavity = 1;
				} else {
					$val_cavity = $tab_fct_inventory_fg->cavity;
				}
				
				if($tab_fct_inventory_fg->man_power == 0){
					$val_man_power = 1;
				} else {
					$val_man_power = $tab_fct_inventory_fg->man_power;
				}
				
				if(empty($tab_fct_inventory_fg_pair->item_code)){
					$val_pair_code = 0;										
				} else {
					$val_pair_code = 1;
				}
				
				if($val_pair_code == 0){
					$cal_cycle_time = $val_cycle_time_raw/$val_cavity;
				} else {
					$cal_cycle_time = $val_cycle_time_raw;
				}
				
			} else {
				
				$val_cavity = $tab_fct_inventory_fg->cavity;
				
				if($tab_fct_inventory_fg->man_power == 0){
					$val_man_power = 1;
				} else {
					$val_man_power = $tab_fct_inventory_fg->man_power;
				}
				
				$cal_cycle_time = $val_cycle_time_raw/$val_man_power;
			}
			
			$sum_forecast_div_selected = DB::table('cap_forecast_divs')->where('item_code',$val_item_code_srt)->sum('quantity');	
			
			$tab_line_distribution_new = DB::table('cap_line_distributions')->where('item_code',$val_item_code_srt)->where('priority',1)->first();
			if(empty($tab_line_distribution_new->line_code)){
				$val_line_code_first = "none";
			} else {
				$val_line_code_first = $tab_line_distribution_new->line_code;
			}
					
			$tab_line_list_one = DB::table('inv_line_lists')->where('line_code',$val_line_code_first)->first();
			if(empty($tab_line_list_one->category)){
				$val_category = "none";
			} else {
				$val_category = $tab_line_list_one->category;
			}
			
			$cal_total_forecast_time = $cal_cycle_time * $sum_forecast_div_selected / 60;
			
			$ins_items = array('item_code' => $val_item_code_srt, 'line_category' => $val_category, 'quantity' => $sum_forecast_div_selected, 
			'departement' => $val_departement, 'cavity' => $val_cavity, 'man_power' => $val_man_power, 'total_forecast_time' => $cal_total_forecast_time,
			'cycle_time_raw' => $val_cycle_time_raw, 'cycle_time' => $cal_cycle_time, 'pair' => $val_pair, 'counter_forecast' => $sum_forecast_div_selected);		
			CapItem::insert($ins_items);
		}

        return redirect()->route("step2");
    }

    public function step2()
    {
        return view("production.capacity_forecast.step2");
    }

    public function step2logic(Request $request)
    {

        DB::table('cap_line_capacities')->truncate();
		DB::table('cap_line_summaries')->truncate();
	
		$tab_line_list = DB::table('inv_line_lists')->get();
		
		foreach($tab_line_list as $fore_line_list){
			
			$val_line_code = $fore_line_list->line_code;
			$val_departement = $fore_line_list->departement;
			
			if($val_departement == "390"){
				$cal_time_limit = 26400;
			} else {
				$cal_time_limit = 8400;
			}
			
			$ins_line_capacity = array('line_code' => $val_line_code, 'departement' => $val_departement, 'time_limit' => $cal_time_limit, 'status' => 0);		
			CapLineCapacity::insert($ins_line_capacity);			
		}
        return redirect()->route("step3");
    }

    public function step3()
    {
        //algoritma step 3

        return view("production.capacity_forecast.step3");
    }

    public function step3logic(Request $request)
    {
        DB::table('cap_results')->truncate();
	
		for($i = 1;$i <= 9;$i++){
			
			$tab_line_capacity = DB::table('cap_line_capacities')->get();
			
			foreach($tab_line_capacity as $line_capacity){
								
				$val_line_code = $line_capacity->line_code;				
								
				$tab_line_distribution = DB::table('cap_line_distributions')->where('line_code',$val_line_code)->where('priority',$i)->orderBy('id','asc')->get();
				
				foreach($tab_line_distribution as $line_distribution){
					$val_distribution_id = $line_distribution->id;
					$val_item_code = $line_distribution->item_code;
					
					$tab_items = DB::table('cap_items')->where('item_code',$val_item_code)->first();
					$val_item_id = $tab_items->id;
					$val_cycle_time = $tab_items->cycle_time;
					$val_counter_forecast = $tab_items->counter_forecast;
					
					if($val_counter_forecast > 0){			
						$tab_line_capacity_ref = DB::table('cap_line_capacities')->where('line_code',$val_line_code)->first();
						$val_time_limit = $tab_line_capacity_ref->time_limit;
						
						if($val_time_limit > 0){
							
							$cal_max_prod = $val_time_limit/$val_cycle_time;
							$cal_max_prod_u = ceil($cal_max_prod);
							
							if($cal_max_prod_u >= $val_counter_forecast){
								$cal_forecast_qty = $val_counter_forecast;
								$cal_time_limit = ($cal_max_prod_u-$val_counter_forecast)*$val_cycle_time;
								$cal_counter_forecast_new = 0;
							} else {
								$cal_forecast_qty = $cal_max_prod_u;
								$cal_time_limit = 0;
								$cal_counter_forecast_new = $val_counter_forecast-$cal_max_prod_u;
							}
							
							//Input Data ke dalam tabel results
							$update_pr_item = DB::table('cap_items')->where('id',$val_item_id)->update([																															
								'counter_forecast' => $cal_counter_forecast_new,																																	
							]);
							
							$update_pr_line = DB::table('cap_line_capacities')->where('line_code',$val_line_code)->update([										
								'time_limit' => $cal_time_limit,																		
							]);	

							DB::table('cap_results')->insert([								
								'line_code' => $val_line_code,
								'mould_code' => $val_item_code,
								'forecast_qty' => $cal_forecast_qty,
								'cycle_time' => $val_cycle_time,
								'production_time' => $cal_forecast_qty*$val_cycle_time,
								'balance' => $cal_time_limit,
							]);
						}
					}
				}
				
			}

		}			
        return redirect()->route('step3logiclast');
    }

    public function step3logiclast(Request $request)
    {
        $tab_line_list_category = DB::table('inv_line_lists')->select('category')->distinct()->get();
		
		foreach($tab_line_list_category as $line_category){
			
			$val_category = $line_category->category;
			
			$con_line_category = DB::table('inv_line_lists')->where('category',$val_category)->count();

			if($val_category == "ASSY"){
				$val_departement = "362";
			} elseif($val_category == "PRINTING"){
				$val_departement = "361";
			} elseif($val_category == "SPRAY"){
				$val_departement = "361";
			} elseif($val_category == "SPRAY C/R"){
				$val_departement = "361";
			} elseif($val_category == "SPRAY ROBOT"){
				$val_departement = "361";
			} else {
				$val_departement = "390";
			}
			
			$tab_datelist = DB::table('uti_date_list')->where('id',8)->first();
			$val_work_day = $tab_datelist->additional_value_dec;
			
			//$val_work_day = 23.5;
			
			if($val_departement == "390"){
				$val_hour = 24;
			} else {
				$val_hour = 8;
			}
			
			$cal_ready_time = $val_work_day * $con_line_category * $val_hour;
			$val_efficiency = 85;
			$cal_max_capacity = $cal_ready_time * ($val_efficiency / 100);					
			
			$sum_req_hour = DB::table('cap_items')->where('line_category',$val_category)->sum('total_forecast_time');
			
			$cal_capacity_req_percent = $sum_req_hour / $cal_max_capacity * 100;
			$cal_capacity_req_percent_ceil = ceil($cal_capacity_req_percent);
			
			$ins_line_summary = array('department' => $val_departement, 'line_category' => $val_category, 'line_quantity' => $con_line_category, 'work_day' => $val_work_day,
			'ready_time' => $cal_ready_time, 'efficiency' => $val_efficiency, 'max_capacity' => $cal_max_capacity, 'capacity_req_hour' => $sum_req_hour,
			'capacity_req_percent' => $cal_capacity_req_percent_ceil);		
			CapLineSummary::insert($ins_line_summary);
			
		}		

        return redirect()->route('capacityforecastindex');
    }

}
