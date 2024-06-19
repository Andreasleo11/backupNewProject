<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use DateTime;
use Carbon\Carbon;

use App\Models\delsched_delfilter;
use App\Models\delsched_delsum;
use App\Models\delsched_final;
use App\Models\delsched_finalwip;
use App\Models\delsched_solist;
use App\Models\delsched_stock;
use App\Models\delsched_stockwip;
use App\Models\SapDelsched;
use App\Models\SapInventoryMtr;

use App\Models\DelschedFinal;
use App\Models\DelschedFinalWip;
use App\DataTables\DeliveryNewTableDataTable;
use App\DataTables\WipFinalDsDataTable;
use App\DataTables\SapDelschedDataTable;

class DeliveryScheduleController extends Controller
{
    public function index(DeliveryNewTableDataTable $dataTable)
    {
        // $datas = DelschedFinal::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render("business.dsnewindex");
    }

    public function indexfinal(WipFinalDsDataTable $dataTable)
    {
        // $datas = DelschedFinalWip::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render("business.dsnewindexwip");
    }


	public function indexraw(SapDelschedDataTable $dataTable)
    {
        // $datas = DelschedFinalWip::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render("business.rawdelsched");
    }


	public function averageschedule()
	{
		$data = SapDelsched::all();
		// Mengelompokkan data berdasarkan bulan dan item_code, kemudian menghitung total quantity
		$today = now();
		$currentMonth = $today->format('Y-m');

		// Filter the data to only include the current month
			$data = $data->filter(function($item) use ($currentMonth) {
				return Carbon::parse($item->delivery_date)->format('Y-m') === $currentMonth;
			});

			// Group the data by month and item_code, then calculate total quantity
			$itemCounts = $data->groupBy(function($item) {
				return Carbon::parse($item->delivery_date)->format('Y-m');
			})->map(function($group) {
				return $group->groupBy('item_code')->map(function($itemGroup) {
					// Count unique item codes
					return $itemGroup->count();
				});
			});

			// Fetch the SapInventoryMtr data
			$inventoryData = SapInventoryMtr::all();

			// Map the inventory data based on fg_code
			$inventoryMap = $inventoryData->keyBy('fg_code');

			// Combine the grouped data with the inventory data
			$result = $itemCounts->map(function($group) use ($inventoryMap) {
				return $group->map(function($count, $itemCode) use ($inventoryMap) {
					// Get the corresponding inventory data
					$inventory = $inventoryMap->get($itemCode);

					// If inventory data exists, return the in_stock value
					return $inventory ? $inventory->in_stock : null;
				});
			});

			// Calculate total quantities for the current month
			$totalQuantities = $data->groupBy(function($item) {
				return Carbon::parse($item->delivery_date)->format('Y-m');
			})->map(function($group) {
				return $group->groupBy('item_code')->map(function($itemGroup) {
					return $itemGroup->sum('delivery_qty');
				});
			});




		return view("business.averageschedule", compact('data','itemCounts', 'totalQuantities', 'result'));
	}

    public function step1()
    {
        DB::table('delsched_final')->truncate();
		DB::table('delsched_solist')->truncate();
		DB::table('delsched_delfilter')->truncate();
		DB::table('delsched_delsum')->truncate();
		DB::table('delsched_stock')->truncate();
		DB::table('delsched_finalwip')->truncate();
		DB::table('delsched_stockwip')->truncate();

        $tab_sap_delsched = DB::table('sap_delsched')->orderBy('delivery_date','asc')->orderBy('item_code','asc')->get();

        foreach($tab_sap_delsched as $sap_delsched){

			$val_item_code_i = $sap_delsched->item_code;
			$val_delivery_date_i = $sap_delsched->delivery_date;
			$val_delivery_qty_i = $sap_delsched->delivery_qty;
			$val_so_number_i = $sap_delsched->so_number;

			$tab_sap_inventoryfg = DB::table('sap_inventory_fg')->where('item_code',$val_item_code_i)->first();
			$val_item_name = $tab_sap_inventoryfg->item_name;
			$val_packaging = $tab_sap_inventoryfg->packaging;
			$val_standar_packaging = $tab_sap_inventoryfg->standar_packing;
			$val_process_owner = $tab_sap_inventoryfg->process_owner;
			if($val_process_owner == 'INJ'){
				$val_departement = 390;
			} elseif($val_process_owner == 'SEC'){
				$val_departement = 361;
			} else {
				$val_departement = 362;
			}

			$tab_sap_customer = DB::table('sap_fg_customers')->where('item_code',$val_item_code_i)->first();
			if(empty($tab_sap_customer->item_code)){
				$val_customer_code = '';
				$val_customer_name = '';
			} else {
				$val_customer_code = $tab_sap_customer->customer_code;
				$val_customer_name = $tab_sap_customer->customer_name;
			}

			$ins_final = array('delivery_date' => $val_delivery_date_i,
			'item_code' => $val_item_code_i,
			'item_name' => $val_item_name,
			'delivery_qty' => $val_delivery_qty_i,
			'so_number' => $val_so_number_i,
			'doc_status' => 'O',
			'packaging_code' => $val_packaging,
			'standar_pack' => $val_standar_packaging,
			'customer_code' => $val_customer_code,
			'customer_name' => $val_customer_name,
			'departement' => $val_departement);
			DelschedFinal::insert($ins_final);
		}

        $tab_sap_delso = DB::table('sap_delso')->orderBy('doc_num','asc')->orderBy('item_no','asc')->get();

		foreach($tab_sap_delso as $sap_delso){

			$val_so_num_ii = $sap_delso->doc_num;
			$val_so_status_ii = $sap_delso->doc_status;
			$val_item_code_ii = $sap_delso->item_no;
			$val_so_quantity_ii = $sap_delso->quantity;
			$val_delivered_qty_ii = $sap_delso->delivered_qty;
			$val_row_status_ii = $sap_delso->row_status;

			$ins_solist = array('so_number' => $val_so_num_ii, 'so_status' => $val_so_status_ii, 'item_code' => $val_item_code_ii, 'so_qty' => $val_so_quantity_ii, 'delivered_qty' => $val_delivered_qty_ii, 'row_status' => $val_row_status_ii);
			delsched_solist::insert($ins_solist);

		}

        $tab_sap_delactual = DB::table('sap_delactual')->get();

		foreach($tab_sap_delactual as $sap_delactual){

			$val_item_code_iii = $sap_delactual->item_no;
			$val_delivery_date_iii = $sap_delactual->delivery_date;
			$val_quantity_iii = $sap_delactual->quantity;
			$val_so_num_iii = $sap_delactual->so_num;

			$tab_delsched_solist_iii = DB::table('delsched_solist')->where('so_number',$val_so_num_iii)->first();
			if(empty($tab_delsched_solist_iii->so_status)){
				$rcd_status = 'O';
			} else {
				if($tab_delsched_solist_iii->so_status == 'O'){
					$rcd_status = 'O';
				} else {
					$rcd_status = 'C';
				}
			}

			if($rcd_status == 'O'){
				$ins_delfilter = array('item_code' => $val_item_code_iii, 'delivery_date' => $val_delivery_date_iii, 'quantity' => $val_quantity_iii, 'so_number' => $val_so_num_iii);
				delsched_delfilter::insert($ins_delfilter);
			}
		}


        $tab_delsched_delfilter = DB::table('delsched_delfilter')->select('item_code')->distinct()->get();

		foreach($tab_delsched_delfilter as $delsched_delfilter){

			$val_item_code_iv = $delsched_delfilter->item_code;

			$sum_delsched_delfilter_qty = DB::table('delsched_delfilter')->where('item_code',$val_item_code_iv)->sum('quantity');

			$ins_delsum = array('item_code' => $val_item_code_iv, 'quantity' => $sum_delsched_delfilter_qty, 'total_after' => $sum_delsched_delfilter_qty);
			delsched_delsum::insert($ins_delsum);
		}

		//Close 1.4

		return redirect()->route('deslsched.step2');
    }

    public function step2()
    {
        //2. Filter delsched_final by SO yang close
		$tab_delsched_final = DB::table('delsched_final')->where('so_number','<>','')->get();

		foreach($tab_delsched_final as $delsched_final){
			$val_final_id = $delsched_final->id;
			$val_so_number = $delsched_final->so_number;
			$val_delivery_qty = $delsched_final->delivery_qty;

			$tab_solist = DB::table('delsched_solist')->where('so_number',$val_so_number)->first();
			$val_so_status = $tab_solist->so_status ?? null;

			if($val_so_status == 'C'){

				$update_final = DB::table('delsched_final')->where('id', $val_final_id)->update([
					'delivered' => $val_delivery_qty,
					'outstanding' => 0,
					'outstanding_stk' => 0,
					'doc_status' => 'C',
					'status' => 'success',
				]);

			}

		}

		return redirect()->route('deslsched.step3');;
    }

    public function step3()
    {
        	//3. Filter delsched dengan pengurangan delivery yang sudah dilakukan dari delsum
		$tab_delsched_final = DB::table('delsched_final')->where('doc_status','=','O')->orderBy('id','asc')->get();

		foreach($tab_delsched_final as $delsched_final){
			$val_final_id = $delsched_final->id;
			$val_item_code = $delsched_final->item_code;
			$val_delivery_qty = $delsched_final->delivery_qty;

			$tab_delsched_delsum = DB::table('delsched_delsum')->where('item_code',$val_item_code)->first();

			if(empty($tab_delsched_delsum->item_code)){
				$val_total_after = 0;
			} else {
				$val_total_after = $tab_delsched_delsum->total_after;
			}

			if($val_total_after <= 0){

				$update_final = DB::table('delsched_final')->where('id', $val_final_id)->update([
					'delivered' => 0,
					'outstanding' => $val_delivery_qty,
					'outstanding_stk' => $val_delivery_qty,
					'status' => 'danger',
				]);

			} else {

				if($val_total_after >= $val_delivery_qty){

					$cal_outstanding = 0;
					$cal_total_after_now = $val_total_after - $val_delivery_qty;

					if(empty($tab_delsched_delsum)){
					} else {
						$val_delsum_id = $tab_delsched_delsum->id;
						$update_delsum = DB::table('delsched_delsum')->where('id', $val_delsum_id)->update([
							'total_after' => $cal_total_after_now,
						]);
					}

					$update_final = DB::table('delsched_final')->where('id', $val_final_id)->update([
						'delivered' => $val_delivery_qty,
						'outstanding' => 0,
						'outstanding_stk' => 0,
						'status' => 'success',
					]);

				} else {

					$cal_outstanding = $val_delivery_qty - $val_total_after;
					$cal_total_after_now = 0;

					if(empty($tab_delsched_delsum)){
					} else {
						$val_delsum_id = $tab_delsched_delsum->id;
						$update_delsum = DB::table('delsched_delsum')->where('id', $val_delsum_id)->update([
							'total_after' => $cal_total_after_now,
						]);
					}

					$update_final = DB::table('delsched_final')->where('id', $val_final_id)->update([
						'delivered' => $cal_outstanding,
						'outstanding' => $cal_outstanding,
						'outstanding_stk' => $cal_outstanding,
						'status' => 'warning',
					]);

				}
			}

		}

		return redirect()->route('deslsched.step4');
    }

    public function step4()
    {

		$tab_delsched_final_item = DB::table('delsched_final')->select('item_code')->distinct()->get();

		foreach($tab_delsched_final_item as $delsched_final_item){

			$val_item_code = $delsched_final_item->item_code;

			$tab_sap_inventoryfg = DB::table('sap_inventory_fg')->where('item_code','=',$val_item_code)->first();
			$val_stock = $tab_sap_inventoryfg->stock;

			$ins_stock = array('item_code' => $val_item_code, 'quantity' => $val_stock, 'total_after' => $val_stock);
			delsched_stock::insert($ins_stock);

		}

		$tab_delsched_final = DB::table('delsched_final')->orderBy('id','asc')->get();

		foreach($tab_delsched_final as $delsched_final){

			$date_now = Carbon::now();

			$val_final_id = $delsched_final->id;
			$val_item_code_final = $delsched_final->item_code;
			$val_status = $delsched_final->status;
			$val_delivery_date = $delsched_final->delivery_date;

			$tab_delsched_stock = DB::table('delsched_stock')->where('item_code','=',$val_item_code_final)->first();
			$val_stock_id = $tab_delsched_stock->id;
			$val_stock_new = $tab_delsched_stock->quantity;
			$val_total_after = $tab_delsched_stock->total_after;

			if($val_status == 'success'){

				$update_final = DB::table('delsched_final')->where('id', $val_final_id)->update([
					'stock' => $val_stock_new,
					'balance' => $val_total_after,
				]);

			} else {

				$val_outstanding_st = $delsched_final->outstanding;

				if($val_total_after < 0){

					$cal_outstanding_st = $val_outstanding_st;
					$cal_total_after_now = $val_total_after - $val_outstanding_st;

					if($val_delivery_date <= $date_now){
						$rcd_status = 'danger';
					} else {
						$rcd_status = 'light';
					}

				} else {

					if($val_total_after >= $val_outstanding_st){

						$cal_outstanding_st = 0;
						$cal_total_after_now = $val_total_after - $val_outstanding_st;

						if($val_delivery_date <= $date_now){
							$rcd_status = 'warning';
						} else {
							$rcd_status = 'light';
						}

					} else {

						$cal_outstanding_st = $val_outstanding_st-$val_total_after;
						$cal_total_after_now = $val_total_after-$val_outstanding_st;

						if($val_delivery_date <= $date_now){
							$rcd_status = 'danger';
						} else {
							$rcd_status = 'light';
						}

					}
				}

				$update_stock = DB::table('delsched_stock')->where('id', $val_stock_id)->update([
					'total_after' => $cal_total_after_now,
				]);

				$update_final = DB::table('delsched_final')->where('id', $val_final_id)->update([
					'stock' => $val_stock_new,
					'balance' => $cal_total_after_now,
					'outstanding_stk' => $cal_outstanding_st,
					'status' => $rcd_status,
				]);

			}

		}

		$now = new DateTime();
		$now->modify('+420 minutes');

		$tb_datelist = DB::table('uti_date_list')->where('id','13')->update([
			'updated_at' => $now,
		]);

		return redirect()->route('indexds');
    }

    public function step1wip()
    {
        //0. Format data dalam table tertentu
		DB::table('delsched_finalwip')->truncate();
		DB::table('delsched_stockwip')->truncate();

		//1. Menarik data dari final menjadi wip ke dalam finalwip
		$tab_delsched_final = DB::table('delsched_final')->where('status','=','danger')->orWhere('status','=','light')->orderBy('id','asc')->get();

		foreach($tab_delsched_final as $delsched_final){
			$val_id = $delsched_final->id;
			$val_delivery_date = $delsched_final->delivery_date;
			$val_so_number = $delsched_final->so_number;
			$val_customer_code = $delsched_final->customer_code;
			$val_customer_name = $delsched_final->customer_name;
			$val_item_code = $delsched_final->item_code;
			$val_item_name = $delsched_final->item_name;
			$val_outstanding_stk = $delsched_final->outstanding_stk;

			$tab_sap_bom_wip_check = DB::table('sap_bom_wip')->where('fg_code','=',$val_item_code)->first();

			if(empty($tab_sap_bom_wip_check->fg_code)){
			} else {
				$tab_sap_bom_wip = DB::table('sap_bom_wip')->where('fg_code','=',$val_item_code)->get();

				foreach($tab_sap_bom_wip as $sap_bom_wip){
					$val_semi_first = $sap_bom_wip->semi_first;
					$val_semi_second = $sap_bom_wip->semi_second;
					$val_semi_third = $sap_bom_wip->semi_third;
					$val_bom_qty_first = $sap_bom_wip->qty_first;
					$val_bom_qty_second = $sap_bom_wip->qty_second;
					$val_bom_qty_third = $sap_bom_wip->qty_third;
					$val_level = $sap_bom_wip->level;

					if($val_level == 3){

						$rcd_bom_qty = $val_bom_qty_first*$val_bom_qty_second*$val_bom_qty_third;
						$cal_req_qty = $rcd_bom_qty*$val_outstanding_stk;
						$rcd_wip = 	$val_semi_third;

					} elseif($val_level == 2){

						$rcd_bom_qty = $val_bom_qty_first*$val_bom_qty_second;
						$cal_req_qty = $rcd_bom_qty*$val_outstanding_stk;
						$rcd_wip = 	$val_semi_second;

					} else {

						$rcd_bom_qty = $val_bom_qty_first;
						$cal_req_qty = $rcd_bom_qty*$val_outstanding_stk;
						$rcd_wip = 	$val_semi_first;

					}

					$tab_sap_inventory_fg = DB::table('sap_inventory_fg')->where('item_code','=',$rcd_wip)->first();

					$val_wip_name = $tab_sap_inventory_fg->item_name;
					$val_stock = $tab_sap_inventory_fg->stock;
					$val_process_owner = $tab_sap_inventory_fg->process_owner;
					if($val_process_owner == 'INJ'){
						$val_departement = 390;
					} elseif($val_process_owner == 'SEC'){
						$val_departement = 361;
					} else {
						$val_departement = 362;
					}

					$ins_finalwip = array('fglink_id' => $val_id, 'delivery_date' => $val_delivery_date, 'so_number' => $val_so_number, 'customer_code' => $val_customer_code,'customer_name' => $val_customer_name,
					'item_code' => $val_item_code, 'item_name' => $val_item_name, 'outstanding_del' => $val_outstanding_stk, 'wip_code' => $rcd_wip, 'wip_name' => $val_wip_name, 'departement' => $val_departement,
					'bom_level' => $val_level, 'bom_quantity' => $rcd_bom_qty, 'req_quantity' => $cal_req_qty, 'stock_wip' => $val_stock, 'balance_wip' => $val_stock, 'status' => 'light');
					DelschedFinalWip::insert($ins_finalwip);

				}


				/*
				$ins_finalwip = array('delivery_date' => $val_delivery_date, 'so_number' => $val_so_number, 'customer_code' => $val_customer_code,
				'customer_name' => $val_customer_name, 'item_code' => $val_item_code, 'item_name' => $val_item_name, 'outstanding_del' => $val_outstanding_stk);
				delsched_finalwip::insert($ins_finalwip);
				*/

			}
		}

		return redirect()->route('delschedwip.step2');
    }

    public function step2wip()
    {
        //2. Filter delsched_finalwip dengan penarikan dari stok
		$tab_delsched_finalwip_item = DB::table('delsched_finalwip')->select('wip_code')->distinct()->get();

		foreach($tab_delsched_finalwip_item as $delsched_finalwip_item){

			$val_wip_code = $delsched_finalwip_item->wip_code;

			$tab_sap_inventoryfg = DB::table('sap_inventory_fg')->where('item_code','=',$val_wip_code)->first();
			$val_stock = $tab_sap_inventoryfg->stock;

			$ins_stockwip = array('item_code' => $val_wip_code, 'quantity' => $val_stock, 'total_after' => $val_stock);
			delsched_stockwip::insert($ins_stockwip);

		}

		$tab_delsched_finalwip = DB::table('delsched_finalwip')->orderBy('id','asc')->get();

		foreach($tab_delsched_finalwip as $delsched_finalwip){

			$date_now = Carbon::now();

			$val_finalwip_id = $delsched_finalwip->id;
			$val_wip_code_finalwip = $delsched_finalwip->wip_code;
			$val_delivery_date = $delsched_finalwip->delivery_date;
			$val_req_quantity = $delsched_finalwip->req_quantity;
			$val_outstanding_wip = $delsched_finalwip->outstanding_wip;

			$tab_delsched_stockwip = DB::table('delsched_stockwip')->where('item_code','=',$val_wip_code_finalwip)->first();
			$val_stockwip_id = $tab_delsched_stockwip->id;
			$val_stockwip_new = $tab_delsched_stockwip->quantity;
			$val_total_after = $tab_delsched_stockwip->total_after;

			if($val_total_after < 0){

				$cal_outstanding_wip_new = $val_req_quantity;
				$cal_total_after_now = $val_total_after-$val_req_quantity;

				if($val_delivery_date <= $date_now){
						$rcd_status = 'danger';
				} else {
					$rcd_status = 'light';
				}

			} else {
				if($val_total_after >= $val_req_quantity){

					$cal_outstanding_wip_new = 0;
					$cal_total_after_now = $val_total_after-$val_req_quantity;

					if($val_delivery_date <= $date_now){
							$rcd_status = 'danger';
					} else {
						$rcd_status = 'light';
					}

				} else {

					$cal_outstanding_wip_new = $val_req_quantity-$val_total_after;
					$cal_total_after_now = $val_total_after-$val_req_quantity;

					if($val_delivery_date <= $date_now){
						$rcd_status = 'danger';
					} else {
						$rcd_status = 'light';
					}

				}
			}

			$update_stock = DB::table('delsched_stockwip')->where('id', $val_stockwip_id)->update([
					'total_after' => $cal_total_after_now,
				]);

			$update_final = DB::table('delsched_finalwip')->where('id', $val_finalwip_id)->update([
				'stock_wip' => $val_stockwip_new,
				'balance_wip' => $cal_total_after_now,
				'outstanding_wip' => $cal_outstanding_wip_new,
				'status' => $rcd_status,
			]);
		}

		$now = new DateTime();
		$now->modify('+420 minutes');

		$tb_datelist = DB::table('uti_date_list')->where('id','14')->update([
			'updated_at' => $now,
		]);

		$this->statusFinish();

		return redirect()->route('indexfinalwip');
    }





	public function statusFinish()
	{
		 // Retrieve all records from the delsched_final table
		 $datas = delsched_final::get();

		 $datas2 = delsched_finalwip::get();

		 $today = Carbon::today();
		//  dd($today);

		 foreach ($datas as $data) {
			 // Check if doc_status is 'C' or if delivery_qty is equal to delivered
			 if ($data->doc_status === 'C' || $data->delivery_qty === $data->delivered) {
				// Update the status to 'Finish' if the doc_status is 'C' or if delivery_qty equals delivered
				$data->status = 'Finish';
			} elseif ($data->doc_status === 'O' && $today->diffInDays($data->delivery_date, false) == 2) {
				// Update the status to 'Danger' if the doc_status is 'O' and the delivery_date is 2 days from today
				$data->status = 'Danger';
			} elseif ($data->doc_status === 'O' && $today->diffInDays($data->delivery_date, false) == -2) {
				// Update the status to 'Warning' if the doc_status is 'O' and the delivery_date was 2 days ago
				$data->status = 'Warning';
			} else {
				// For all other conditions, set the status to 'Warning'
				$data->status = 'Warning';
			}
			
	

			$data->save();
		 }


		 foreach ($datas2 as $dataw) {
			// Check if doc_status is 'C' or if delivery_qty is equal to delivered

			if($dataw->balance_wip < 0) {
				// Update the status to 'Finish'
			   $dataw->status = 'Warning';
		   	}

			if($dataw->balance_wip > 0) {
				 // Update the status to 'Finish'
				$dataw->status = 'Finish';
			}

			if($dataw->balance_wip < 0 && $today->diffInDays($dataw->delivery_date, false) == 5) {
				// Update the status to 'Danger'
				$dataw->status = 'Danger';
			}
			
			
		//    if($today->diffInDays($dataw->delivery_date, false) == -5) {
		// 	   // Update the status to 'Warning'
		// 	   $dataw->status = 'Warning';
		//    }
		   
   

		   $dataw->save();
		}
	 
		 
	 
		 // Output the updated data for verification
		 return redirect()->route('indexfinalwip');
	}
}

