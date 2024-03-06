<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeTrainingDetail;
use App\Models\EmployeeTrainingHeader;

class EmployeeTrainingController extends Controller
{
    public function home()
    {
        return view('employeetraining.index');
    }

    public function create()
    {
        return view('employeetraining.create');
    }

    public function post(Request $request)
    {
        $data = $request->all();
       

        $header = EmployeeTrainingHeader::create([
            'name' => $request->input('name'),
            'nik' => $request->input('nik'),
            'department' => $request->input('department'),
            'mulai_bekerja' => $request->input('mulai_bekerja'),
        ]);


        if ($request->has('trainings') && is_array($request->input('trainings'))) {
            foreach ($request->input('trainings') as $trainingData) {
                // Initialize is_internal and is_external flags
                $is_internal = false;
                $is_external = false;
    
                // Loop through types array
                foreach ($trainingData['types'] as $type) {
                    if ($type === 'internal') {
                        $is_internal = true;
                    } elseif ($type === 'external') {
                        $is_external = true;
                    }
                }
    
                // Set is_internal and is_external flags based on conditions
                if ($is_internal && $is_external) {
                    $is_internal = true;
                    $is_external = true;
                }
    
                // Create EmployeeTrainingDetail instance with appropriate attributes
                EmployeeTrainingDetail::create([
                    'header_id' => $header->id,
                    'training_name' => $trainingData['training_name'],
                    'training_date' => $trainingData['training_date'],
                    'is_internal' => $is_internal,
                    'is_external' => $is_external,
                    'result' => $trainingData['hasil_pelatihan'],
                    'information' => $trainingData['keterangan'],
                ]);
            }
        }
        return redirect()->route('training.index');
    }

    public function list()
    {
        $data = EmployeeTrainingHeader::get();

        return view('employeetraining.list',compact('data'));
    }

    public function detail($id)
    {
        $data = EmployeeTrainingHeader::with('trainingDetail')->find($id);
        // dd($data);
        
        return view('employeetraining.detail', compact('data'));
    }

}
