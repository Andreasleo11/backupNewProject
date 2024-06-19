<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Department;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;


use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Redirect;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Maatwebsite\Excel\Sheet;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Storage;



use App\Imports\OvertimeImport;
use App\Exports\OvertimeExport;
use Maatwebsite\Excel\Facades\Excel;



use Illuminate\Support\Facades\Auth;

class FormOvertimeController extends Controller
{
    public function index()
    {
       // Get the authenticated user
       $user = Auth::user();

       // Filter the data based on the user's departement_id
       $dataheader = HeaderFormOvertime::with('Relationuser', 'Relationdepartement')
           ->where('dept_id', $user->department_id)
           ->whereHas('details', function ($query) {
            // Condition to filter out headers without valid details
            $query->whereNotNull('start_date')
                  ->whereNotNull('end_date')
                  ->where('start_date', '<>', '0000-00-00')
                  ->where('end_date', '<>', '0000-00-00');
        })
           ->get();

       return view("formovertime.index", compact("dataheader"));
    }

    public function create()
    {
        $employees = Employee::get();
        $departements = Department::get();

        return view("formovertime.create", compact("employees", "departements"));
    }


    public function getEmployees(Request $request)
    {
        $nama = $request->query('name');
        $nik = $request->query('nik');
        $deptid = $request->query('deptid');

        info('AJAX request received for item name: ' . $nama);
        info('AJAX request received for nik: ' . $nik);
        info('AJAX request received for dept id: ' . $deptid);

        $department = Department::where('id', $deptid)->first();
        $dept_no = $department ? $department->dept_no : null;


        // Fetch item names and prices from the database based on user input
        if ($dept_no) {
            if($nik){
                // Fetch employees based on NIK and department number
                $pegawais = Employee::where('NIK', 'like', '%' . $nik . '%')
                    ->where('Dept', $dept_no)
                    ->select('NIK', 'nama')
                    ->get();

            } elseif($nama) {
                // Fetch employees based on Nama and department number
                $pegawais = Employee::where('Nama', 'like', '%' . $nama . '%')
                    ->where('Dept', $dept_no)
                    ->select('NIK', 'nama')
                    ->get();
            }
            return response()->json($pegawais);
        } else {
            // If department number does not exist, return an empty response or handle error
            return response()->json([], 404);
        }
    }


    public function insert(Request $request)
    {
        $uploadedFiles = $request->file('excel_file');

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

        if ($uploadedFiles) {
        
            $this->importFromExcel($request, $headerovertime->id);

        }else{
            $this->detailOvertimeInsert($request, $headerovertime->id);

        }

        return redirect()->route('formovertime.index');
    }


    public function importFromExcel($request, $headerOvertimeId)
    {
        $path = $request->file('excel_file')->store('temp');
        $import = new OvertimeImport($headerOvertimeId);
        Excel::import($import, $path);
        
    }

    public function detailOvertimeInsert($request, $id)
    {
        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $employeedata) {
                $nik = $employeedata['NIK'];
                $nama = $employeedata['nama'];
                $jobdesc = $employeedata['jobdesc'];
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


        $employees = Employee::get();
        $departements = Department::get();
        // dd($header);
        return view("formovertime.detail", compact("header", "datas", "employees", "departements"));
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
                $headerForm->status = 9;
            }
            if (!empty($headerForm->autograph_4)) {
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




    public function exportOvertime($headerId)
    {
        $header = HeaderFormOvertime::with('Relationdepartement')->find($headerId);
        $datas = DetailFormOvertime::where('header_id', $headerId)->get();

        $departmentName = $header->Relationdepartement->name;
        $currentDate = Carbon::now()->format('d-m-y'); // or any format you prefer

        $fileName = "overtime_{$departmentName}_{$currentDate}.xlsx";


        return Excel::download(new OvertimeExport($header, $datas), $fileName);
    }

    public function edit($id)
    {
        $header = HeaderFormOvertime::with('Relationuser','Relationdepartement')->find($id);

        $datas = DetailFormOvertime::Where('header_id', $id)->get();

        $employees = Employee::get();
        $departements = Department::get();

        return view("formovertime.edit", compact("header", "datas", "employees", "departements"));
    }

    public function update(Request $request, $id)
    {
        $data = $request->all();
        // dd($data);

        DetailFormOvertime::where('header_id', $id)->delete();

        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $employeedata) {
                $nik = $employeedata['NIK'];
                $nama = $employeedata['nama'];
                $jobdesc = $employeedata['jobdesc'];
                $startdate = $employeedata['startdate'];
                $starttime = $employeedata['starttime'];
                $enddate = $employeedata['enddate'];
                $endtime = $employeedata['endtime'];
                $break = $employeedata['break'];
                $remark = $employeedata['remark'];

                DetailFormOvertime::create([
                    'header_id' => $id,
                    'NIK' => $nik,
                    'nama' => $nama,
                    'job_desc' => $jobdesc,
                    'start_date' => $startdate,
                    'start_time' => $starttime,
                    'end_date' => $enddate,
                    'end_time' => $endtime,
                    'break' => $break,
                    'remarks' => $remark
                ]);
            }
        }

        return redirect()->route('formovertime.detail', ['id' => $id])
        ->with('success', 'Form Overtime updated successfully.');
    }



}
