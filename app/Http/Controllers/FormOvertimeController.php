<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 100000);

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Department;
use App\Models\DetailFormOvertime;
use App\Models\HeaderFormOvertime;
use App\Models\ActualOvertimeDetail;
use App\Exports\OvertimeExport;
use App\Exports\OvertimeExportExample;
use App\Models\OvertimeFormApproval;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Exports\OvertimeSummaryExport;

class FormOvertimeController extends Controller
{
    public function index(Request $request)
    {
        HeaderFormOvertime::doesntHave('details')->delete();
        $user = Auth::user();
        $dataheaderQuery = HeaderFormOvertime::with('user', 'department', 'details');

        $dataheaderQuery->where(function ($query) use ($user, $request) {
            // Now everything is scoped properly here

            if ($user->role->name === 'SUPERADMIN') {
                $query->with('approvals.step');
            } elseif ($user->specification->name === 'VERIFICATOR') {
                $query->where(function ($subQuery) {
                    $subQuery->where('is_approve', 1)
                        ->orWhere(function ($q) {
                            $q->where('status', 'waiting-dept-head')
                                ->whereHas('department', function ($qq) {
                                    $qq->where('name', 'PERSONALIA');
                                });
                        });
                });
            } elseif ($user->specification->name === 'DIRECTOR') {
                $query->where('status', 'waiting-director');
            } elseif ($user->is_gm) {
                $query->where('status', 'waiting-gm');
                $query->where('branch', $user->name === 'pawarid' ? 'Karawang' : 'Jakarta');
            } elseif ($user->is_head) {
                $query->where(function ($q) use ($user) {
                    $q->where('dept_id', $user->department->id)
                        ->orWhereHas('department', function ($qq) use ($user) {
                            if ($user->department->name === 'LOGISTIC') {
                                $qq->where('name', 'STORE');
                            }
                        });
                });
                $query->where('status', 'waiting-dept-head');
            } else {
                if ($user->name === 'Umi') {
                    $query->whereIn('dept_id', [1, 2]);
                } else {
                    $query->where('dept_id', $user->department_id);
                }
            }

            // Additional filters
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
                $query->whereHas('details', function ($q) use ($startDate, $endDate) {
                    $q->whereDate('start_date', '>=', $startDate)
                        ->whereDate('start_date', '<=', $endDate);
                });
            }

            if ($request->filled('dept')) {
                $query->where('dept_id', $request->dept);
            }
          
            if ($request->filled('status') && $user->specification->name === 'VERIFICATOR') {
                $query->where('is_push', $request->status);
            }

            // Include personal entries too
            $query->orWhere('user_id', $user->id);
        });

        // === FILTER TAMBAHAN ===
        // Check if both start_date and end_date are provided by the user
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $dataheaderQuery->whereHas('details', function ($query) use ($startDate, $endDate) {
                $query->whereDate('start_date', '>=', $startDate)
                    ->whereDate('start_date', '<=', $endDate);
            });
        }

        if ($request->filled('dept')) {
            $dataheaderQuery->where('dept_id', $request->dept);
        }

        if ($request->filled('status') && $user->specification->name === 'VERIFICATOR') {
            $dataheaderQuery->where('is_push', $request->status);
        }
      
        if ($request->filled('info_status')) {
            $status = $request->input('info_status');

            $dataheaderQuery->whereHas('details', function ($q) use ($status) {
                if ($status === 'pending') {
                    $q->whereNull('status');
                } elseif ($status === 'approved') {
                    $q->where('status', 'Approved');
                } elseif ($status === 'rejected') {
                    $q->where('status', 'Rejected');
                }
            });
        }

        if($request->filled('is_push')){
            $dataheaderQuery->where('is_push', $request->is_push);
        }

        $dataheader = $dataheaderQuery
            ->orderBy('id', 'desc')
            ->paginate(10);

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
  
    public function detail($id)
    {
        $header = HeaderFormOvertime::with('user', 'department', 'approvals', 'approvals.step')->find($id);
        $datas = DetailFormOvertime::with('actualOvertimeDetail')->Where('header_id', $id)->get();
        $employees = Employee::get();
        $departements = Department::get();

        // dd($header);
        return view("formovertime.detail", compact("header", "datas", "employees", "departements"));
    }

    public function sign(Request $request, $id)
    {
        $username = Auth::user()->name;
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
                'status' => 'rejected',
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
            'OTDate'      => Carbon::parse($detail->overtime_date)->format('d/m/Y'),
            'JobDesc'     => Str::limit($detail->job_desc, 250),
            'Department'  => $employee->organization_structure ?? 0,
            'StartDate'   => Carbon::parse($detail->start_date)->format('d/m/Y'),
            'StartTime'   => Carbon::parse($detail->start_time)->format('H:i'),
            'EndDate'     => Carbon::parse($detail->end_date)->format('d/m/Y'),
            'EndTime'     => Carbon::parse($detail->end_time)->format('H:i'),
            'BreakTime'   => $detail->break,
            'Remark'      => Str::limit("({$detail->NIK}) Reference from LINE {$detail->id} ID {$header->id}", 250),
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
                'OTDate'      => Carbon::parse($detail->overtime_date)->format('d/m/Y'),
                'JobDesc'     => Str::limit($detail->job_desc, 250),
                'Department'  => $employee->organization_structure ?? 0,
                'StartDate'   => Carbon::parse($detail->start_date)->format('d/m/Y'),
                'StartTime'   => Carbon::parse($detail->start_time)->format('H:i'),
                'EndDate'     => Carbon::parse($detail->end_date)->format('d/m/Y'),
                'EndTime'     => Carbon::parse($detail->end_time)->format('H:i'),
                'BreakTime'   => $detail->break,
                'Remark'      => Str::limit("({$detail->NIK}) Reference from LINE {$detail->id} ID {$header->id}", 250),
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

    public function showForm()
    {
        return view('formovertime.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $file = $request->file('file');
        $data = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet = $data->getActiveSheet();
        $rows = $sheet->toArray();

        DB::beginTransaction();
        try {
            foreach ($rows as $index => $row) {
                // Skip header rows (row index 0 - 3) dan baris tanpa kolom 1
                if ($index < 4 || empty($row[0])) continue;

                // Ambil key dari kolom pertama
                if (preg_match('/LINE\s*(\d+)\s*ID/', $row[0], $matches)) {
                    $key = intval($matches[1]);
                } else {
                    continue;
                }

                $voucher = $row[1];
                $in_date = $row[2] ?? null;
                $in_time = $row[3] ?? null;
                $out_date = $row[4] ?? null;
                $out_time = $row[5] ?? null;
                $nett = $row[6] ?? null;
                
                $in_date_formatted = null;
                $out_date_formatted = null;
                $in_time_formatted = null;
                $out_time_formatted = null;

                // In Date
                if (!empty($in_date)) {
                    if (is_numeric($in_date)) {
                        $in_date_formatted = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($in_date)->format('Y-m-d');
                    } elseif (preg_match('/\d{2}\/\d{2}\/\d{4}/', $in_date)) {
                        try {
                            $in_date_formatted = Carbon::createFromFormat('d/m/Y', $in_date)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $in_date_formatted = null;
                        }
                    }
                }

                // Out Date
                if (!empty($out_date)) {
                    if (is_numeric($out_date)) {
                        $out_date_formatted = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($out_date)->format('Y-m-d');
                    } elseif (preg_match('/\d{2}\/\d{2}\/\d{4}/', $out_date)) {
                        try {
                            $out_date_formatted = Carbon::createFromFormat('d/m/Y', $out_date)->format('Y-m-d');
                        } catch (\Exception $e) {
                            $out_date_formatted = null;
                        }
                    }
                }

                // In Time
                $in_time_formatted = is_numeric($in_time)
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($in_time)->format('H:i:s')
                    : (!empty($in_time) ? $in_time : null);

                // Out Time
                $out_time_formatted = is_numeric($out_time)
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($out_time)->format('H:i:s')
                    : (!empty($out_time) ? $out_time : null);

                // Netto
                $nett_overtime = is_numeric($nett) ? floatval($nett) : null;

                // Simpan ke DB
                ActualOvertimeDetail::updateOrCreate(
                    ['key' => $key],
                    [
                        'voucher' => strval($voucher),
                        'in_date' => $in_date_formatted,
                        'in_time' => $in_time_formatted,
                        'out_date' => $out_date_formatted,
                        'out_time' => $out_time_formatted,
                        'nett_overtime' => $nett_overtime
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'File berhasil diimpor.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors('Gagal impor: ' . $e->getMessage());
        }
    }
}
