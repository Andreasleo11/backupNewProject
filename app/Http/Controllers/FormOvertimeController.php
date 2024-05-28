<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Employee;
use App\Models\Department;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use Illuminate\Support\Facades\Auth;

class FormOvertimeController extends Controller
{
    public function index()
    {
        $dataheader = HeaderFormOvertime::with('Relationuser','Relationdepartement')->get();
        // dd($dataheader);
        return view("formovertime.index", compact("dataheader"));
    }

    public function create()
    {
        $employees = Employee::get();
        $departements = Department::get();
        
        return view("formovertime.create", compact("employees", "departements"));
    }


    public function getEmployeeNik(Request $request)
    {
        $nik = $request->query('nik');
       
        info('AJAX request received for item name: ' . $nik);

        // Fetch item names and prices from the database based on user input
        $pegawais = Employee::where('NIK', 'like', "%" . $nik . "%")
        ->select('NIK', 'nama')
        ->get();


        
        return response()->json($pegawais);
    }


    public function insert(Request $request)
    {
        // dd($request->all());
        $userIdCreate = Auth::id();
        $deptId = $request->input('from_department');

        $department = Department::find($deptId);

        $status = 1;

        if ($department && $department->name === 'MOULDING') {
            $status = 6;
        }


        
        $headerData = [
            'user_id' => $userIdCreate,
            'dept_id' => $request->input('from_department'),
            'create_date' => $request->input('date_form_overtime'),
            'autograph_1' => strtoupper(Auth::user()->name) . '.png',
            'status' => $status,
            'is_design' => $request->input('design')
        ];
        
        // dd($headerData);
        $headerovertime = HeaderFormOvertime::create($headerData);

        $this->detailOvertimeInsert($request, $headerovertime->id);
        
        return redirect()->route('formovertime.index');
    }

    public function detailOvertimeInsert($request, $id)
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $employeedata) {
                $nik = $employeedata['NIK'];
                $nama = $employeedata['nama'];
                $jobdesc = $employeedata['jobdesc'];
                $makan = $employeedata['makan'];
                $startdate = $employeedata['startdate'];
                $starttime = $employeedata['starttime'];
                $enddate = $employeedata['enddate'];
                $endtime = $employeedata['endtime'];
                $break = $employeedata['break'];
                $remark = $employeedata['remark'];

                $detailData = [
                    'header_id' => $id,
                    'NIK' => $nik,
                    'nama' => $nama,
                    'is_makan' => $makan,
                    'job_desc' => $jobdesc,
                    'start_date' => $startdate,
                    'start_time' => $starttime,
                    'end_date' => $enddate,
                    'end_time' => $endtime,
                    'break' => $break,
                    'remarks' => $remark
                ];

                DetailFormOvertime::create($detailData);
            }
        }
    }


    public function detail($id)
    {
        $header = HeaderFormOvertime::with('Relationuser','Relationdepartement')->find($id);
        
        $datas = DetailFormOvertime::Where('header_id', $id)->get();
        // dd($header);
        return view("formovertime.detail", compact("header", "datas"));
    }

    public function saveAutographOtPath(Request $request, $id, $section)
    {
        $username = Auth::user()->name;
        // Log::info('Username:', ['username' => $username]); 
        $imagePath = $username . '.png';
        // Log::info('imagepath : ', $imagePath);

        // Save $imagePath to the database for the specified $reportId and $section
        $report = HeaderFormOvertime::find($id);
            $report->update([
                "autograph_{$section}" => $imagePath
            ]);

        $this->updateStatus($id);

        return response()->json(['success' => 'Autograph saved successfully!']);
    }




    public function reject(Request $request, $id)
    {
        $request->validate([
            'description' => 'required'
        ]);

        $data = HeaderFormOvertime::find($id);
        HeaderFormOvertime::find($id)->update([
                'description' => $request->description,
                'is_approve' => false,
            ]);
        
        

        return redirect()->route('director.qaqc.index')->with('success', 'Report rejected!');
    }

    
    public function updateStatus($id)
    {
        $headerForm = HeaderFormOvertime::find($id);

        if (!$headerForm) {
            return response()->json(['error' => 'HeaderFormOvertime not found'], 404);
        }

        $department = $headerForm->Relationdepartement;

        if (!$department) {
            return response()->json(['error' => 'Related department not found'], 404);
        }

        if ($department->is_office) {
            // Case 1: is_office is true
            $headerForm->status = 1;
            if (!empty($headerForm->autograph_2)) {
                $headerForm->status = 2;
            }
            if (!empty($headerForm->autograph_3)) {
                $headerForm->status = 5;
                $headerForm->is_approve = 1;
            }
        } elseif ($department->name === 'MOULDING') {
            // Case 2: department name is MOULDING
            $headerForm->status = 6;
            if (!empty($headerForm->autograph_2)) {
                $headerForm->status = 1;
            }
            if (!empty($headerForm->autograph_3)) {
                $headerForm->status = 2;
            }
            if (!empty($headerForm->autograph_4)) {
                $headerForm->status = 5;
                $headerForm->is_approve = 1;
            }
        } else {
            // Case 3: is_office is false
            $headerForm->status = 1;
            if (!empty($headerForm->autograph_2)) {
                $headerForm->status = 3;
            }
            if (!empty($headerForm->autograph_3)) {
                $headerForm->status = 2;
            }
            if (!empty($headerForm->autograph_4)) {
                $headerForm->status = 5;
                $headerForm->is_approve = 1;
            }
        }

        $headerForm->save();

        return response()->json(['message' => 'Status updated successfully', 'data' => $headerForm], 200);
    }


}
