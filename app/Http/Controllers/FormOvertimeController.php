<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Department;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use App\Imports\OvertimeImport;
use App\Exports\OvertimeExport;
use App\Exports\OvertimeExportExample;
use App\Models\ApprovalFlow;
use App\Models\OvertimeFormApproval;
use App\Models\User;
use App\Notifications\FormOvertimeNotification;
use App\Support\ApprovalFlowResolver;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;

class FormOvertimeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $dataheaderQuery = HeaderFormOvertime::with('user', 'department');

        // === FILTER BERDASARKAN ROLE USER ===
        if ($user->specification->name === 'VERIFICATOR') {
            $dataheaderQuery->where('is_approve', 1);
        } elseif ($user->specification->name === 'DIRECTOR') {
            $dataheaderQuery->where('status', 'waiting-director');
        } elseif ($user->is_gm) {
            $dataheaderQuery
                ->where('status', 'waiting-gm');
        } elseif ($user->is_head) {
            $dataheaderQuery->where('dept_id', $user->department->id);

            if ($user->department->name === 'LOGISTIC') {
                $dataheaderQuery->orWhere(function ($query) {
                    $query->whereHas('department', function ($query) {
                        $query->where('name', 'STORE');
                    });
                });
            }

            $dataheaderQuery->where('status', 'waiting-dept-head');
        } else {
              if ($user->name === 'Umi') {
                    $dataheaderQuery->whereIn('dept_id', [1, 2]);
                } else {
                    $dataheaderQuery->where('dept_id', $user->department_id);
                }
        }

        // === FILTER TAMBAHAN ===
        if ($request->filled('date')) {
            $dataheaderQuery->whereDate('create_date', $request->input('date'));
        }

        if ($request->filled('dept')) {
            $dataheaderQuery->where('dept_id', $request->input('dept'));
        }

        if ($request->filled('status') && $user->specification->name === 'VERIFICATOR') {
            $dataheaderQuery->where('is_push', $request->input('status'));
        }

        $dataheader = $dataheaderQuery
            ->orderBy('id', 'desc')
            ->orWhere('user_id', $user->id)
            ->get();

        $departments = Department::all();

        return view("formovertime.index", compact("dataheader", "departments"));
    }


    public function create()
    {
        $employees = Employee::get();
        $departements = Department::get();

        return view("formovertime.create", compact("employees", "departements"));
    }

    public function downloadTemplate()
    {
        return Excel::download(new OvertimeExportExample(), 'overtime_template.xlsx');
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
                    ->whereNull('end_date')
                    ->select('NIK', 'nama')
                    ->get();
            } elseif ($nama) {
                // Fetch employees based on Nama and department number
                $pegawais = Employee::where('Nama', 'like', '%' . $nama . '%')
                    ->where('Dept', $dept_no)
                    ->whereNull('end_date')
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

        $deptId = $request->input('from_department');
        $branch = $request->input('branch');
        $date = $request->input('date_form_overtime');   // e.g. "2025-06-04"
        $isPlanned = $request->filled('date_form_overtime')
            && Carbon::parse($date)->greaterThan(  // later than…
                now()->startOfDay()               // …the very end of today
            );

        $headerData = [
            'user_id' => Auth::id(),
            'dept_id' => $deptId,
            'create_date' => $request->input('date_form_overtime'),
            'branch' => $branch,
            'is_design' => $request->input('design'),
            'is_export' =>  0,
            'is_planned' => $isPlanned,
            'status' => 'waiting-creator',
        ];

        $flowSlug = ApprovalFlowResolver::for($headerData);
        $flow = ApprovalFlow::where('slug', $flowSlug)->firstOrFail();

        $headerData['approval_flow_id'] = $flow->id;

        $headerovertime = HeaderFormOvertime::create($headerData);

        // Pre-seed pending rows
        foreach ($flow->steps as $step) {
            $headerovertime->approvals()->create([
                'flow_step_id' => $step->id,
                'status'       => 'pending',
            ]);
        }

        if ($uploadedFiles) {
            $this->importFromExcel($request, $headerovertime->id);
        } else {
            $this->detailOvertimeInsert($request, $headerovertime->id);
        }

        $this->sendNotification($headerovertime);

        return redirect()->route('formovertime.detail', $headerovertime->id)->with('success', 'Overtime created successfully!');
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
        $header = HeaderFormOvertime::with('user', 'department', 'approvals', 'approvals.step')->find($id);
        $datas = DetailFormOvertime::Where('header_id', $id)->get();
        $employees = Employee::get();
        $departements = Department::get();

        // dd($header);
        return view("formovertime.detail", compact("header", "datas", "employees", "departements"));
    }

    // public function saveAutographOtPath(Request $request, $id, $section)
    // {
    //     $username = Auth::user()->name;
    //     // Log::info('Username:', ['username' => $username]);
    //     $imagePath = $username . '.png';
    //     // Log::info('imagepath : ', $imagePath);

    //     // Save $imagePath to the database for the specified $reportId and $section
    //     $report = HeaderFormOvertime::find($id);
    //     $report->update([
    //         "autograph_{$section}" => $imagePath
    //     ]);

    //     $this->updateStatus($id);

    //     return response()->json(['success' => 'Autograph saved successfully!']);
    // }

    public function updateStatus($id)
    {
        $headerForm = HeaderFormOvertime::find($id);

        if (!$headerForm) {
            return response()->json(['error' => 'HeaderFormOvertime not found'], 404);
        }

        $department = $headerForm->department;

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

        //  $this->sendNotification($headerForm);

        return response()->json(['message' => 'Status updated successfully', 'data' => $headerForm], 200);
    }

    public function sign(Request $request, $id)
    {
        // dd($id);
        // dd($request->step_id);
        $username = Auth::user()->name;
        // Log::info('Username:', ['username' => $username]);
        $imagePath = $username . '.png';

        $form = HeaderFormOvertime::find($id);
        $approval = $form->approvals()
            ->where('flow_step_id', $request->step_id)
            ->firstOrFail();
        // dd($form->currentStep());
        $approval->update([
            'status'         => 'approved',
            'signed_at'      => now(),
            'approver_id'    => auth()->id(),
            'signature_path' => $imagePath,
        ]);

        // Update form status if final step

        if ($form->currentStep() === null) {
            $form->update(['status' => 'approved']);
        } elseif ($form->nextStep()) {
            $status = 'waiting-' . str_replace('_', '-', $form->nextStep()->role_slug);
            $form->update(['status' => $status]);
        } else {
            $form->update(['status' => 'Unknown']);
        }

        return redirect()->back()->with('success', 'Form signed successfully.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'description' => 'required'
        ]);

        HeaderFormOvertime::find($id)
            ->update([
                'description' => $request->description,
                'is_approve' => false,
            ]);

        OvertimeFormApproval::find($request->approval_id)
            ->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Report rejected!');
    }

    private function sendNotification($report)
    {
        $director = User::whereHas('specification', function ($query) {
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
            case 'waiting-dept-head':
                if ($report->department->name === 'STORE') {
                    $user = User::where('is_head', 1)->whereHas('department', function ($query) {
                        $query->where('name', 'LOGISTIC');
                    })->first();
                } elseif ($report->department->name === 'SECOND PROCESS') {
                    $user = User::where('email', 'imano@daijo.co.id')->first();
                } else {
                    $user = $deptHead;
                }
                $status = 'Waiting for Dept Head';
                break;
            // Send to Verificator
            case 'waiting-verificator':
                $user = $verificator;
                $status = 'Waiting to Verificator';
                break;
            // Send to GM
            case 'waiting-gm':
                $user = $gm;
                $status = 'Waiting for GM';
                break;
            // Send to Director
            case 'waiting-director':
                $user = $director;
                $status = 'Waiting for Director';
                break;
            // Send to Supervisor
            case 'waiting-supervisor':
                $user = $supervisor;
                $status = 'Waiting for Supervisor';
                break;
            default:
                return redirect()->back()->with('error', 'Failed send notification!');
                break;
        }

        $formattedCreateDate = \Carbon\Carbon::parse($report->create_date)->format('d-m-Y');
        $cc = [$report->user->email];

        if ($report->is_approve === 1 || $report->is_approve === 0) {
            $user = $report->user;
            array_push($cc, $verificator);
        }

        $details = [
            'greeting' => 'Form Overtime Notification',
            'body' => "We waiting for your sign for this report : <br>
                    - Report ID : $report->id <br>
                    - Department From : {$report->department->name} ({$report->department->dept_no}) <br>
                    - Create Date : {$formattedCreateDate} <br>
                    - Created By : {$report->user->name} <br>
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
        $header = HeaderFormOvertime::with('department')->find($headerId);
        $datas = DetailFormOvertime::where('header_id', $headerId)->get();

        $departmentName = $header->department->name;
        $currentDate = Carbon::now()->format('d-m-y'); // or any format you prefer

        $fileName = "overtime_{$departmentName}_{$currentDate}.xlsx";

        $header->update(['is_export' => true]);
        return Excel::download(new OvertimeExport($header, $datas), $fileName);
    }

    public function edit($id)
    {
        $header = HeaderFormOvertime::with('user', 'department')->find($id);

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
        return redirect()->back()->with('success', 'Form Overtime deleted successfully!');
    }

    public function destroyDetail($id)
    {
        DetailFormOvertime::find($id)->delete();
        return redirect()->back()->with('success', 'Form Overtime Detail deleted successfully!');
    }

    public function pushSingleDetailToJPayroll($detailId, Request $request)
    {
        $action = $request->query('action'); // 'approve' atau 'reject'

        $detail = DetailFormOvertime::with('employee', 'header')->find($detailId);

        if (!$detail) {
            return response()->json(['error' => 'Detail tidak ditemukan'], 404);
        }

        if ($detail->header->is_push == 1) {
            return response()->json(['error' => 'Header sudah dipush'], 400);
        }

        // Kalau aksi adalah reject, langsung update status-nya
        if ($action === 'reject') {
            $detail->status = 'Rejected';
            $detail->save();

            $this->checkAndUpdateHeaderPushStatus($detail->header_id);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil direject',
            ]);
        }



        // Jika bukan approve, maka tidak valid
        if ($action !== 'approve') {
            return response()->json(['error' => 'Aksi tidak valid'], 400);
        }

        $header = $detail->header;
        $employee = $detail->employee;

        $payload = [
            'OTType'      => '1',
            'OTDate'      => Carbon::parse($header->create_date)->format('d/m/Y'),
            'JobDesc'     => $detail->job_desc,
            'Department'  => $employee->organization_structure ?? 0,
            'StartDate'   => Carbon::parse($detail->start_date)->format('d/m/Y'),
            'StartTime'   => Carbon::parse($detail->start_time)->format('H:i'),
            'EndDate'     => Carbon::parse($detail->end_date)->format('d/m/Y'),
            'EndTime'     => Carbon::parse($detail->end_time)->format('H:i'),
            'BreakTime'   => $detail->break,
            'Remark'      => $detail->remarks,
            'Choice'      => '1',
            'CompanyArea' => '10000',
            'EmpList'     => [
            'NIK1' => $detail->NIK
            ]
        ];

        $url = 'http://192.168.6.75/JPayroll/thirdparty/ext/API_Store_Overtime.php';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Basic QVBJPUV4VCtEQCFqMDpEQCFqMEBKcDR5cjAxMQ==',
                'Content-Type' => 'application/json'
            ])->post($url, $payload);

            $responseData = [
                'NIK' => $detail->NIK,
                'status' => $response->status(),
                'body' => $response->body()
            ];

            $responseJson = json_decode($response->body(), true);
        
            if ($response->successful() && isset($responseJson['status']) && $responseJson['status'] == '200') {
                $detail->is_processed = 1;
                $detail->status = 'Approved';
                $detail->save();

                $this->checkAndUpdateHeaderPushStatus($detail->header_id);

                Log::info("✅ Success push for detail ID: $detailId", $responseData);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dipush & diapprove',
                    'response' => $responseData
                ]);
            } else {
                Log::warning("⚠️ Push rejected for detail ID: $detailId - JPayroll response not success", $responseData);

                return response()->json([
                    'success' => false,
                    'message' => 'Push ditolak oleh JPayroll: Data Karyawan sudah ada - Error Note: ' . ($responseJson['msg'] ?? 'Unknown error'),
                    'response' => $responseData
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error("❌ Exception push for detail ID: $detailId", [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi exception saat push',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function pushAllDetailsToJPayroll($headerId)
    {
        $header = HeaderFormOvertime::with('details.employee')->find($headerId);

        if (!$header) {
            return response()->json(['error' => 'Header tidak ditemukan'], 404);
        }

        if ($header->is_push == 1) {
            return response()->json(['error' => 'Header sudah dipush sebelumnya'], 400);
        }

        $url = 'http://192.168.6.75/JPayroll/thirdparty/ext/API_Store_Overtime.php';

        $successCount = 0;
        $failed = [];

        foreach ($header->details as $detail) {
            // Skip jika statusnya Rejected
            if ($detail->status === 'Rejected') {
                continue;
            }

            $employee = $detail->employee;

            $payload = [
                'OTType'      => '1',
                'OTDate'      => Carbon::parse($header->create_date)->format('d/m/Y'),
                'JobDesc'     => $detail->job_desc,
                'Department'  => $employee->organization_structure ?? 0,
                'StartDate'   => Carbon::parse($detail->start_date)->format('d/m/Y'),
                'StartTime'   => Carbon::parse($detail->start_time)->format('H:i'),
                'EndDate'     => Carbon::parse($detail->end_date)->format('d/m/Y'),
                'EndTime'     => Carbon::parse($detail->end_time)->format('H:i'),
                'BreakTime'   => $detail->break,
                'Remark'      => $detail->remarks,
                'Choice'      => '1',
                'CompanyArea' => '10000',
                'EmpList'     => [
                    'NIK1' => $detail->NIK
                ]
            ];

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Basic QVBJPUV4VCtEQCFqMDpEQCFqMEBKcDR5cjAxMQ==',
                    'Content-Type' => 'application/json'
                ])->post($url, $payload);

                $responseData = [
                    'NIK' => $detail->NIK,
                    'status' => $response->status(),
                    'body' => $response->body()
                ];

                if ($response->successful() && isset($responseJson['status']) && $responseJson['status'] == '200') {
                    $detail->is_processed = 1;
                    $detail->status = 'Approved';
                    $detail->save();

                    $this->checkAndUpdateHeaderPushStatus($detail->header_id);

                    Log::info("✅ Success push for detail ID: $detailId", $responseData);

                    return response()->json([
                        'success' => true,
                        'message' => 'Data berhasil dipush & diapprove',
                        'response' => $responseData
                    ]);
                } else {
                    Log::warning("⚠️ Push rejected for detail ID: $detailId - JPayroll response not success", $responseData);

                    return response()->json([
                        'success' => false,
                        'message' => 'Push ditolak oleh JPayroll: Data Karyawan sudah ada - Error Note: ' . ($responseJson['msg'] ?? 'Unknown error'),
                        'response' => $responseData
                    ], 400);
                }
            } catch (\Exception $e) {
                Log::error("❌ Exception push for detail ID: $detail->id", [
                    'error' => $e->getMessage()
                ]);
                $failed[] = [
                    'detail_id' => $detail->id,
                    'NIK' => $detail->NIK,
                    'reason' => 'Exception: ' . $e->getMessage()
                ];
            }
        }

        // Setelah semua proses, cek apakah semua detail sudah diapprove atau direject
        $this->checkAndUpdateHeaderPushStatus($headerId);

        return response()->json([
            'success' => true,
            'message' => 'Proses push selesai',
            'total_success' => $successCount,
            'total_failed' => count($failed),
            'failed_details' => $failed
        ]);
    }

    private function checkAndUpdateHeaderPushStatus($headerId)
    {
        $header = HeaderFormOvertime::with('details')->find($headerId);

        if (!$header) {
            return false;
        }

        // Cek kalau semua detail punya status (tidak null)
        $hasPending = $header->details->contains(function ($detail) {
            return is_null($detail->status);
        });

        if (!$hasPending) {
            $header->is_push = 1;
            $header->save();
        }

        return !$hasPending;
    }
}
