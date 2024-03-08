<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\trial;
use App\Models\User;

class PEController extends Controller
{
    public function index()
    {
        return view('PE.pe_landing');
    }


    public function trialinput()
    {
        return view('PE.trial_input');
    }

    public function input(Request $request)
    {

        $data = $request->validate([
            'customer' => 'required|string',
            'part_name' => 'required|string',
            'part_no' => 'required|string',
            'model' => 'required|string',
            'cavity' => 'required|string',
            'status_trial' => 'required|string',
            'material' => 'required|string',
            'status_material' => 'required|string',
            'color' => 'required|string',
            'material_consump' => 'required|string',
            'dimension_tooling' => 'nullable|string',
            'member_trial' => 'required|string',
            'request_trial' => 'required|date',
            'trial_date' => 'required|date',
            'time_set_up_tooling' => 'nullable|string',
            'time_setting_tooling' => 'nullable|string',
            'time_finish_inject' => 'nullable|string',
            'time_set_down_tooling' => 'nullable|string',
            'trial_cost' => 'nullable|string',
            'tonage' => '',
            'qty' => 'required|string',
            'adjuster' => 'nullable|string',
        ]);

        $inputdata = new trial;

        $inputdata->customer = $data['customer'];
        $inputdata->part_name = $data['part_name'];
        $inputdata->part_no = $data['part_no'];
        $inputdata->model = $data['model'];
        $inputdata->cavity = $data['cavity'];
        $inputdata->status_trial = $data['status_trial'];
        $inputdata->material = $data['material'];
        $inputdata->status_material = $data['status_material'];
        $inputdata->color = $data['color'];
        $inputdata->material_consump = $data['material_consump'];
        $inputdata->dimension_tooling = $data['dimension_tooling'];
        $inputdata->member_trial = $data['member_trial'];
        $inputdata->request_trial = $data['request_trial'];
        $inputdata->trial_date = $data['trial_date'];
        $inputdata->time_set_up_tooling = $data['time_set_up_tooling'];
        $inputdata->time_setting_tooling = $data['time_setting_tooling'];
        $inputdata->time_finish_inject = $data['time_finish_inject'];
        $inputdata->time_set_down_tooling = $data['time_set_down_tooling'];
        $inputdata->trial_cost = $data['trial_cost'];
        $inputdata->qty = $data['qty'];
        $inputdata->adjuster = $data['adjuster'];

        $inputdata->save();


        return redirect()->route('pe.landing');
    }


    public function view(){
        $trial = trial::get();

        return view('PE.pe_trial_list', compact('trial'));
    }


    public function detail($id)
    {
        $trial = Trial::find($id);
        $user =  Auth::user();


        return view('PE.pe_trial_detail', compact('trial','user'));
    }

    public function updateTonage(Request $request, $id)
    {
        $request->validate([
            'tonage' => 'required|string',
        ]);

        $trial = Trial::find($id);

        if (Auth::user()->department->name === 'PI') {
            // Update the tonage
            $trial->tonage = $request->input('tonage');
            $trial->save();
        }

        return redirect()->route('trial.detail', ['id' => $trial->id]); // Redirect back to the landing page
    }

    public function saveSignature(Request $request, $trialId, $section){

        $username = Auth::check() ? Auth::user()->name : '';
        $imagePath = $username . '.png';

        // Save $imagePath to the database for the specified $reportId and $section
        $trial = Trial::find($trialId);
            $trial->update([
                "autograph_{$section}" => $imagePath
            ]);
            $trial->update([
                "autograph_user_{$section}" => $username
            ]);

        return response()->json(['message' => 'Image path saved successfully']);
    }
}
