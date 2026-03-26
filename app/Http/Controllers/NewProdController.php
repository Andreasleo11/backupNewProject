<?php

namespace App\Http\Controllers;

use App\Domain\Production\Services\ProductionPlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewProdController extends Controller
{
    public function __construct(
        private readonly ProductionPlanningService $planningService
    ) {
        $this->middleware('auth');
    }

    public function pps_wizard()
    {
        return view('program/new_production/pps_wizard/pps_wizard');
    }

    public function pps_wizard_step_0_dept()
    {
        $dateInj = DB::table('uti_date_list')->where('id', 15)->first();
        $dateSnd = DB::table('uti_date_list')->where('id', 2)->first();
        $dateAsm = DB::table('uti_date_list')->where('id', 3)->first();

        return view('program/new_production/pps_wizard/pps_wizard_step_0_dept', [
            'date_inj' => $dateInj->last_update,
            'date_snd' => $dateSnd->last_update,
            'date_asm' => $dateAsm->last_update,
        ]);
    }

    public function pps_wizard_step_0_dept_process(Request $request)
    {
        if ($request->scenario == 'INJ') {
            DB::table('prodplan_inj_delitem')->truncate();
            DB::table('prodplan_inj_delraw')->truncate();
            DB::table('prodplan_inj_delsched')->truncate();
            DB::table('prodplan_inj_items')->truncate();
            DB::table('prodplan_inj_linecap')->truncate();
            DB::table('prodplan_inj_linelist')->truncate();

            return redirect()->action([self::class, 'pps_wizard_inj_step_1_scenario']);
        } elseif ($request->scenario == 'SND') {
            return redirect()->action([self::class, 'pps_wizard_snd_step_1_scenario']);
        } else {
            return redirect()->action([self::class, 'pps_wizard_asm_step_1_scenario']);
        }
    }

    public function pps_wizard_inj_step_1_scenario()
    {
        $scenarios = DB::table('prodplan_scenario')->whereIn('id', [1, 2, 3, 4, 5, 6])->get()->keyBy('id');
        $dateList = DB::table('uti_date_list')->where('id', 15)->first();

        return view('program/new_production/pps_wizard/injection/pps_wizard_injection_step_1_scenario', [
            'start_date' => $dateList->start_date,
            'end_date' => $dateList->end_date,
            'lead_time_fg' => $scenarios[1]->val_int_inj,
            'lead_time_wip' => $scenarios[2]->val_int_inj,
            'day_saving' => $scenarios[3]->val_int_inj,
            'man_power' => $scenarios[4]->val_int_inj,
            'mould_change' => $scenarios[5]->val_int_inj,
            'run_forecast' => $scenarios[6]->val_int_inj,
        ]);
    }

    public function pps_wizard_inj_step_1_scenario_process(Request $request)
    {
        DB::table('uti_date_list')
            ->where('id', 15)
            ->update([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

        DB::table('prodplan_scenario')->where('id', 1)->update(['val_int_inj' => $request->lead_time_fg]);
        DB::table('prodplan_scenario')->where('id', 2)->update(['val_int_inj' => $request->lead_time_wip]);
        DB::table('prodplan_scenario')->where('id', 3)->update(['val_int_inj' => $request->day_saving]);
        DB::table('prodplan_scenario')->where('id', 4)->update(['val_int_inj' => $request->man_power]);
        DB::table('prodplan_scenario')->where('id', 5)->update(['val_int_inj' => $request->mould_change]);
        DB::table('prodplan_scenario')->where('id', 6)->update([
            'val_int_inj' => $request->run_forecast == 'Y' ? 1 : 0,
            'val_vc_inj' => $request->run_forecast,
        ]);

        return redirect()->action([self::class, 'pps_wizard_inj_step_2_delsched_process']);
    }

    public function pps_wizard_inj_step_2_delsched_process()
    {
        $this->planningService->processDeliverySchedule();

        return redirect()->action([self::class, 'pps_wizard_inj_step_2_delsched']);
    }

    public function pps_wizard_inj_step_2_delsched_process_ii()
    {
        // Handled in planningService->processDeliverySchedule()
        return redirect()->action([self::class, 'pps_wizard_inj_step_2_delsched_process_iii']);
    }

    public function pps_wizard_inj_step_2_delsched_process_iii()
    {
        // Handled in planningService->processDeliverySchedule()
        return redirect()->action([self::class, 'pps_wizard_inj_step_2_delsched']);
    }

    public function pps_wizard_inj_step_2_delsched()
    {
        $delsched = DB::table('prodplan_inj_delsched')->get();

        return view('program/new_production/pps_wizard/injection/pps_wizard_injection_step_2_delsched', [
            'delsched' => $delsched,
        ]);
    }

    public function pps_wizard_inj_step_3_items_process()
    {
        $this->planningService->processProductionItems();

        return redirect()->action([self::class, 'pps_wizard_inj_step_3_items']);
    }

    public function pps_wizard_inj_step_3_items()
    {
        $items = DB::table('prodplan_inj_items')->get();

        return view('program/new_production/pps_wizard/injection/pps_wizard_injection_step_3_items', [
            'items' => $items,
        ]);
    }
}
