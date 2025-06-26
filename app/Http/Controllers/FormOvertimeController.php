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
use App\Support\ApprovalFlowResolver;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Exports\OvertimeSummaryExport;

class FormOvertimeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $dataheaderQuery = HeaderFormOvertime::with('user', 'department', 'details');

        // === FILTER BERDASARKAN ROLE USER ===
        if ($user->specification->name === 'VERIFICATOR') {
            $dataheaderQuery->where(function ($query) {
                $query->where('is_approve', 1)
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('status', 'waiting-dept-head')
                            ->whereHas('department', function ($subsubQuery) {
                                $subsubQuery->where('name', 'PERSONALIA');
                            });
                    });
            });
        } elseif ($user->specification->name === 'DIRECTOR') {
            $dataheaderQuery->where('status', 'waiting-director');
        } elseif ($user->is_gm) {
            $dataheaderQuery->where('status', 'waiting-gm');
            if ($user->name === 'pawarid') {
                $dataheaderQuery->where('branch', 'Karawang');
            } else {
                $dataheaderQuery->where('branch', 'Jakarta');
            }
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
            $dataheaderQuery->whereDate('create_date', $request->date);
        }

        if ($request->filled('dept')) {
            $dataheaderQuery->where('dept_id', $request->dept);
        }

        if ($request->filled('status') && $user->specification->name === 'VERIFICATOR') {
            $dataheaderQuery->where('is_push', $request->status);
        }

        if (auth()->user()->role->name === 'SUPERADMIN') {
            $dataheader = HeaderFormOvertime::with(['department', 'approvals.step'])->orderBy('id', 'desc')->paginate(10);
            $andriani = User::where('name', 'andriani')->first();

            foreach ($dataheader as $header) {
                if ($header->department?->name !== 'BUSINESS') {
                    continue;
                }

                $needsSave = false;

                foreach ($header->approvals as $approval) {
                    if ($approval->step?->role_slug === 'creator') {
                        $approval->signature_path = 'andriani.png';
                        $approval->approver_id = $andriani->id;
                        $approval->save();
                    }
                }

                // Only update header if needed
                if ($header->user_id !== $andriani->id) {
                    $header->user_id = $andriani->id;
                    $header->save();
                }
            }

            foreach ($dataheader as $header) {
                if ($header->details[0]->start_date > $header->details[0]->created_at) {
                    $header->is_planned = 1;
                    $header->save();
                }
            }
        } else {
            $dataheader = $dataheaderQuery
                ->orderBy('id', 'desc')
                ->orWhere('user_id', $user->id)
                ->paginate(10);
        }


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
        // dd($request->all());
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
        // dd($flowSlug);
        $flow = ApprovalFlow::where('slug', $flowSlug)->firstOrFail();
        // dd($flow);

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
            $createdCount = $this->importFromExcel($request, $headerovertime->id);

            if ($createdCount === 0) {
                $headerovertime->delete();
                return redirect()->route('formovertime.index')
                    ->with('message', 'Tidak ada data valid dari Excel, header dihapus otomatis.');
            }
        } else {
            $result = $this->detailOvertimeInsert($request, $headerovertime->id);
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                return $result;
            }
        }

        // $this->sendNotification($headerovertime);

        return redirect()->route('formovertime.detail', $headerovertime->id)->with('success', 'Overtime created successfully!');
    }


    public function importFromExcel($request, $headerOvertimeId)
    {
        $path = $request->file('excel_file')->store('temp');
        $import = new OvertimeImport($headerOvertimeId);
        Excel::import($import, $path);

        return $import->createdCount;
    }

    public function detailOvertimeInsert($request, $id)
    {
        $createdCount = 0;

        if ($request->has('items') && is_array($request->input('items'))) {
            foreach ($request->input('items') as $employeedata) {
                $nik = $employeedata['NIK'];
                $nama = $employeedata['nama'];
                $overtimedate = $employeedata['overtimedate'];
                $jobdesc = $employeedata['jobdesc'];
                $startdate = $employeedata['startdate'];
                $starttime = $employeedata['starttime'];
                $enddate = $employeedata['enddate'];
                $endtime = $employeedata['endtime'];
                $break = $employeedata['break'];
                $remark = $employeedata['remark'];

                // ✅ Skip jika end_date < start_date
                if (strtotime($enddate) < strtotime($startdate)) {
                    // dd("kena ini kah ?");
                    continue;
                }

                // ✅ Skip jika kombinasi NIK, start_date dan end_date sudah ada
                $exists = DetailFormOvertime::where('NIK', $nik)
                    ->where('overtime_date', $overtimedate)
                    ->exists();


                if ($exists) {
                    // dd("kena ini toh ?");
                    continue;
                }

                $detailData = [
                    'header_id' => $id,
                    'NIK' => $nik,
                    'nama' => $nama,
                    'overtime_date' => $overtimedate,
                    'job_desc' => $jobdesc,
                    'start_date' => $startdate,
                    'start_time' => $starttime,
                    'end_date' => $enddate,
                    'end_time' => $endtime,
                    'break' => $break,
                    'remarks' => $remark
                ];

                DetailFormOvertime::create($detailData);
                $createdCount++;
            }
        }
        if ($createdCount === 0) {
            HeaderFormOvertime::find($id)?->delete();
            return redirect()->route('formovertime.index')
                ->with('message', 'Tidak ada data valid yang dimasukkan, header dihapus otomatis.');
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
            $form->update(['status' => 'approved', 'is_approve' => 1]);
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
            'OTDate'      => Carbon::parse($detail->start_date)->format('d/m/Y'),
            'JobDesc'     => Str::limit($detail->job_desc, 250),
            'Department'  => $employee->organization_structure ?? 0,
            'StartDate'   => Carbon::parse($detail->start_date)->format('d/m/Y'),
            'StartTime'   => Carbon::parse($detail->start_time)->format('H:i'),
            'EndDate'     => Carbon::parse($detail->end_date)->format('d/m/Y'),
            'EndTime'     => Carbon::parse($detail->end_time)->format('H:i'),
            'BreakTime'   => $detail->break,
            'Remark'      => Str::limit('Reference from ID ' . $header->id, 250),
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
            if ($detail->status === 'Rejected') {
                continue;
            }

            if ($detail->status === 'Approved' && $detail->is_processed == 1) {
                continue;
            }

            $employee = $detail->employee;

            $payload = [
                'OTType'      => '1',
                'OTDate'      => Carbon::parse($detail->start_date)->format('d/m/Y'),
                'JobDesc'     => Str::limit($detail->job_desc, 250),
                'Department'  => $employee->organization_structure ?? 0,
                'StartDate'   => Carbon::parse($detail->start_date)->format('d/m/Y'),
                'StartTime'   => Carbon::parse($detail->start_time)->format('H:i'),
                'EndDate'     => Carbon::parse($detail->end_date)->format('d/m/Y'),
                'EndTime'     => Carbon::parse($detail->end_time)->format('H:i'),
                'BreakTime'   => $detail->break,
                'Remark'      => Str::limit('Reference from ID ' . $header->id, 250),
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

                $responseJson = $response->json();
                $responseData = [
                    'NIK' => $detail->NIK,
                    'status' => $response->status(),
                    'body' => $response->body()
                ];

                if ($response->successful() && isset($responseJson['status']) && $responseJson['status'] == '200') {
                    $detail->is_processed = 1;
                    $detail->status = 'Approved';
                    $detail->save();

                    $successCount++;
                    Log::info("✅ Success push for detail ID: {$detail->id}", $responseData);
                } else {
                    $msg = $responseJson['msg'] ?? 'Unknown error';

                    $detail->status = 'Rejected';
                    $detail->reason = "Reject JPAYROLL karena {$msg}";
                    $detail->save();

                    Log::warning("⚠️ Push rejected & status updated for detail ID: {$detail->id}", $responseData);

                    $failed[] = [
                        'detail_id' => $detail->id,
                        'NIK' => $detail->NIK,
                        'reason' => 'Rejected by JPayroll'
                    ];
                }
            } catch (\Exception $e) {
                Log::error("❌ Exception push for detail ID: {$detail->id}", [
                    'error' => $e->getMessage()
                ]);

                $failed[] = [
                    'detail_id' => $detail->id,
                    'NIK' => $detail->NIK,
                    'reason' => 'Exception: ' . $e->getMessage()
                ];
            }
        }

        // Setelah semua proses
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


    public function summaryView(Request $request)
    {
        $summary = collect();

        if ($request->filled(['start_date', 'end_date'])) {
            $request->validate([
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
            ]);

            $data = DetailFormOvertime::query()
                ->whereBetween('start_date', [$request->start_date, $request->end_date])
                ->whereNull('deleted_at')
                ->where('status', 'Approved')
                ->get();

            $grouped = [];
            foreach ($data as $item) {
                $start = Carbon::parse("{$item->start_date} {$item->start_time}");
                $end = Carbon::parse("{$item->end_date} {$item->end_time}");

                if ($end->lessThan($start)) {
                    $end->addDay(); // fallback (shouldn't happen with correct end_date)
                }

                $totalMinutes = $start->diffInMinutes($end) - $item->break;
                $totalHours = $totalMinutes / 60;

                $key = $item->NIK . '|' . $item->nama;

                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'NIK' => $item->NIK,
                        'nama' => $item->nama,
                        'start_date' => $item->start_date,
                        'end_date' => $item->end_date,
                        'total_ot' => $totalHours,
                    ];
                } else {
                    $grouped[$key]['total_ot'] += $totalHours;
                    if ($item->start_date < $grouped[$key]['start_date']) {
                        $grouped[$key]['start_date'] = $item->start_date;
                    }
                    if ($item->end_date > $grouped[$key]['end_date']) {
                        $grouped[$key]['end_date'] = $item->end_date;
                    }
                }
            }

            $summary = collect(array_values($grouped));
        }
        // dd($summary);
        return view('formovertime.export_summary', compact('summary'));
    }

    public function exportSummaryExcel(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        return Excel::download(new OvertimeSummaryExport($request->start_date, $request->end_date), 'Overtime-Summary.xlsx');
    }
}
