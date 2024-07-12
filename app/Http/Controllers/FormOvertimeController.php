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
use App\Models\User;
use App\Notifications\FormOvertimeNotification;
use Maatwebsite\Excel\Facades\Excel;



use Illuminate\Support\Facades\Auth;

class FormOvertimeController extends Controller
{
    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();

        $dataheaderQuery = HeaderFormOvertime::with('Relationuser', 'Relationdepartement');

        // Filter the data based on the user's departement_id
        if ($user->specification->name === 'VERIFICATOR') {
            $dataheaderQuery->where('is_approve', 1);
        } elseif ($user->department->name === 'DIRECTOR') {
            $dataheaderQuery->where('status', 9);
        } elseif ($user->is_gm) {
            $dataheaderQuery
                ->whereNotNull('autograph_2')
                ->whereHas(
                    'Relationdepartement',
                    function ($query) {
                        $query->where('is_office', false)->where(function ($query) {
                            $query->where('name', '!=', 'QA')
                                ->where('name', '!=', 'QC');
                        });
                    }
                );
        } elseif ($user->is_head) {
            $dataheaderQuery->where('dept_id', $user->department->id);

            if ($user->department->name === 'LOGISTIC') {
                $dataheaderQuery->orWhere(function ($query) {
                    $query->whereHas(
                        'Relationdepartement',
                        function ($query) {
                            $query->where('name', 'STORE');
                        }
                    );
                });
            }

            $dataheaderQuery->where('status', 1);
        } else {
            $dataheaderQuery
                ->where('dept_id', $user->department_id);
        }

        $dataheader = $dataheaderQuery
            ->orWhere('user_id', auth()->user()->id)
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
            if ($nik) {
                // Fetch employees based on NIK and department number
                $pegawais = Employee::where('NIK', 'like', '%' . $nik . '%')
                    ->where('Dept', $dept_no)
                    ->select('NIK', 'nama')
                    ->get();
            } elseif ($nama) {
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
        $this->sendNotification($headerovertime);

        if ($uploadedFiles) {

            $this->importFromExcel($request, $headerovertime->id);
        } else {
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
        $header = HeaderFormOvertime::with('Relationuser', 'Relationdepartement')->find($id);

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

        if ($department->name === 'MOULDING') {
            // Case 2: department name is MOULDING
            $headerForm->status = 6;
            if (!empty($headerForm->autograph_2)) {
                $headerForm->status = 1;
            }
            if (!empty($headerForm->autograph_3)) {
                $headerForm->status = 9;
            }
            if (!empty($headerForm->autograph_4)) {
                $headerForm->status = 5;
                $headerForm->is_approve = 1;
            }
        } else if ($department->is_office === 1) {
            // Case 1: is_office is true
            $headerForm->status = 1;
            if (!empty($headerForm->autograph_2)) {
                $headerForm->status = 9;
            }
            if (!empty($headerForm->autograph_3)) {
                $headerForm->status = 5;
                $headerForm->is_approve = 1;
            }
        } else {
            // Case 3: is_office is false
            $headerForm->status = 1;
            if (!empty($headerForm->autograph_2)) {
                $headerForm->status = 3;
                if ($department->name === 'QA' || $department->name === 'QC') {
                    $headerForm->status = 9;
                }
            }
            if (!empty($headerForm->autograph_3)) {
                $headerForm->status = 9;
            }
            if (!empty($headerForm->autograph_4)) {
                $headerForm->status = 5;
                $headerForm->is_approve = 1;
            }
        }

        $headerForm->save();
        $this->sendNotification($headerForm);
        return response()->json(['message' => 'Status updated successfully', 'data' => $headerForm], 200);
    }

    private function sendNotification($report)
    {
        $director = User::whereHas('department', function ($query) {
            $query->where('name', 'DIRECTOR');
        })->first();

        $verificator = User::whereHas('specification', function ($query) {
            $query->where('name', 'VERIFICATOR');
        })->first();

        $gm = User::where('is_gm', 1)->first();

        $supervisor = User::whereHas('specification', function ($query) {
            $query->where('name', 'SUPERVISOR');
        })->first();

        $deptHead = User::where('is_head', 1)->where('department_id', $report->dept_id)->first();

        switch ($report->status) {
                // Send to Dept Head
            case 1:
                if ($report->Relationdepartement->name === 'STORE') {
                    $user = User::where('is_head', 1)->whereHas('department', function ($query) {
                        $query->where('name', 'LOGISTIC');
                    })->first();
                } elseif ($report->Relationdepartement->name === 'SECOND PROCESS') {
                    $user = User::where('email', 'imano@daijo.co.id')->first();
                } else {
                    $user = $deptHead;
                }
                $status = 'Waiting for Dept Head';
                break;
                // Send to Verificator
            case 2:
                $user = $verificator;
                $status = 'Waiting to Verificator';
                break;
                // Send to GM
            case 3:
                $user = $gm;
                $status = 'Waiting for GM';
                break;
                // Send to Director
            case 9:
                $user = $director;
                $status = 'Waiting for Director';
                break;
                // Send to Supervisor
            case 6:
                $user = $supervisor;
                $status = 'Waiting for Supervisor';
                break;
            default:
                abort(500, 'Failed to send notification!');
                break;
        }

        $formattedCreateDate = \Carbon\Carbon::parse($report->create_date)->format('d/m/Y');
        $cc = [$report->Relationuser->email];

        if ($report->is_approve === 1 || $report->is_approve === 0) {
            $user = $report->Relationuser;
            array_push($cc, $verificator);
        }

        $details = [
            'greeting' => 'Form Overtime Notification',
            'body' => "We waiting for your sign for this report : <br>
                    - Report ID : $report->id <br>
                    - Department From : {$report->Relationdepartement->name} ({$report->Relationdepartement->dept_no}) <br>
                    - Create Date : {$formattedCreateDate} <br>
                    - Created By : {$report->Relationuser->name} <br>
                    - Status : {$status} <br>
                        ",
            'cc' => $cc,
            'actionText' => 'Click to see the detail',
            'actionURL' => env('APP_URL', 'http://116.254.114.93:2420/') . 'formovertime/detail/' . $report->id,
        ];

        $user->notify(new FormOvertimeNotification($report, $details));
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
        $header = HeaderFormOvertime::with('Relationuser', 'Relationdepartement')->find($id);

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

    public function destroy($id)
    {
        HeaderFormOvertime::find($id)->delete();
        DetailFormOvertime::where('header_id', $id)->delete();
        return redirect()->back()->with('success', 'Form Overtime Deleted successfully!');
    }
}
