<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 100000);

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeDailyReport;
use App\Models\EmployeeDailyReportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel; // Kalau pakai Excel
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Session;
use Carbon\CarbonPeriod;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeDailyReportController extends Controller
{
    public function index()
    {
        $reports = EmployeeDailyReport::all();

        return view('dailyreport.index', compact('reports'));
    }

    public function showUploadForm()
    {
        return view('dailyreport.upload-daily-report');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'report_file' => 'required|file|mimes:xlsx,csv,txt',
        ]);

        $file = $request->file('report_file');
        $data = Excel::toArray([], $file)[0]; // Sheet pertama

        if (count($data) < 2) {
            return back()->with('error', 'File kosong atau tidak valid.');
        }

        $headerRow = $data[0]; // baris header
        unset($data[0]); // buang header

        $previewData = [];

        foreach ($data as $rowIndex => $row) {
            // Gabungkan kolom berdasarkan header
            $record = [];
            $usedColumns = [];

            foreach ($row as $colIndex => $value) {
                $originalKey = $headerRow[$colIndex] ?? null;
                if ($originalKey === null) continue;

                $key = trim($originalKey);
                // Tambahkan suffix jika nama kolom sudah dipakai
                if (isset($usedColumns[$key])) {
                    $suffix = ++$usedColumns[$key];
                    $key .= '.' . $suffix;
                } else {
                    $usedColumns[$key] = 0;
                }

                $record[$key] = $value;
            }

            if (!isset($record['ID Karyawan (8 Digit) (Contoh: 39001234)'])) continue;

            $employeeIdRaw = trim($record['ID Karyawan (8 Digit) (Contoh: 39001234)']);
            $normalizedId = str_replace([' ', '-'], '', $employeeIdRaw);
            if (!preg_match('/^\d{8}$/', $normalizedId)) continue;

            $department = substr($normalizedId, 0, 3);
            $employeeId = substr($normalizedId, 3);
            $employee = Employee::where('NIK', $employeeId)->first();
            $employeeName = $employee?->Nama ?? 'Unknown';
            $employeeDept = $employee?->Dept ?? 'Unknown';

            try {
                $submittedAt = is_numeric($record['Timestamp'])
                    ? Carbon::instance(Date::excelToDateTimeObject($record['Timestamp']))
                    : Carbon::parse($record['Timestamp']);

                $workDate = is_numeric($record['Tanggal Bekerja'])
                    ? Carbon::instance(Date::excelToDateTimeObject($record['Tanggal Bekerja']))->toDateString()
                    : Carbon::parse($record['Tanggal Bekerja'])->toDateString();
            } catch (\Exception $e) {
                continue;
            }

            for ($i = 1; $i <= 12; $i++) {
                $jamKey = "Jam Sesi $i";
                $descKey = $i === 1 ? "Deskripsi Pekerjaan yang dilakukan" : "Deskripsi Pekerjaan yang dilakukan." . ($i - 1);
                $proofKey = $i === 1 ? "Bukti Pekerjaan" : "Bukti Pekerjaan." . ($i - 1);

                if (empty($record[$jamKey]) || empty($record[$descKey])) {
                    continue;
                }

                $previewData[] = [
                    'submitted_at'        => $submittedAt->format('Y-m-d H:i:s'),
                    'report_type'         => 'Baru',
                    'employee_id'         => $employeeId,
                    'employee_name'       => $employeeName,
                    'departement_id'      => $employeeDept,
                    'work_date'           => $workDate,
                    'work_time'           => trim($record[$jamKey]),
                    'work_description'    => trim($record[$descKey]),
                    'proof_url'           => $record[$proofKey] ?? null,
                ];
            }
        }

        return view('dailyreport.preview', compact('previewData'));
    }

    public function confirmUpload(Request $request)
    {
        $encoded = $request->input('data');
        $rows = unserialize(base64_decode($encoded));
        $logs = [];

        foreach ($rows as $row) {
            $logEntry = $row; // duplicate for log
            $logEntry['logged_at'] = now();

            try {
                $isDuplicate = EmployeeDailyReport::where($row)->exists();

                if ($isDuplicate) {
                    $logEntry['status'] = 'Duplikat';
                    $logEntry['message'] = 'Data sudah ada, dilewati.';
                    $logs[] = $logEntry;
                    EmployeeDailyReportLog::create($logEntry);
                    continue;
                }

                EmployeeDailyReport::create($row);

                $logEntry['status'] = 'Berhasil';
                $logEntry['message'] = null;
                $logs[] = $logEntry;
                EmployeeDailyReportLog::create($logEntry);
            } catch (\Exception $e) {
                $logEntry['status'] = 'Gagal';
                $logEntry['message'] = $e->getMessage();
                $logs[] = $logEntry;
                EmployeeDailyReportLog::create($logEntry);
            }
        }

        return view('dailyreport.upload-log', compact('logs'));
    }


    public function showLoginForm()
    {
        return view('dailyreport.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nik' => 'required',
            'password' => 'required',
        ]);

        $employee = Employee::where('nik', $request->nik)->first();
        // dd($employee); // Debugging line, remove in production

        if (!$employee) {
            return back()->withErrors(['nik' => 'NIK tidak ditemukan.']);
        }

        // Generate expected password: NIK + ddmmyyyy
        $expectedPassword = $employee->NIK . $employee->date_birth->format('dmY');
        // dd($expectedPassword); // Debugging line, remove in production

        if ($request->password === $expectedPassword) {
            // Simpan info login manual ke session
            Session::put('employee_id', $employee->id);
            Session::put('employee_nik', $employee->NIK);

            return redirect()->route('daily-report.user')->with('success', 'Login berhasil!');
        }

        return back()->withErrors(['password' => 'Password salah.']);
    }

    public function dashboardDailyReport(Request $request)
    {
        $employeeNik = Session::get('employee_nik');

        $query = EmployeeDailyReport::where('employee_id', $employeeNik);

        if ($request->filled('filter_date')) {
            $query->whereDate('work_date', $request->filter_date);
        }

        $reports = $query->orderBy('work_date', 'desc')->get();

        return view('dailyreport.dashboard', compact('reports'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['employee_id', 'employee_nik']);
        $request->session()->flush(); // optional kalau mau hapus semua

        return redirect()->route('employee-login')->with('success', 'Logout berhasil.');
    }


    public function indexDepthead(Request $request)
    {
        $user = auth()->user();

        // 1. Authorization check
        if ($user->is_head || $user->specification->name === 'DIRECTOR') {
            // 2. Get department-specific employees from master
            $employeeQuery = Employee::query();
            if ($user->name === 'Bernadett' || $user->specification->name === 'DIRECTOR') {
            } else {
                $employeeQuery->where('Dept', $user->department->dept_no);
            }
            $validEmployees = $employeeQuery->get();
            $validNiks = $validEmployees->pluck('NIK')->toArray();

            // 3. Filtered reports only where NIK and Nama match
            $reportQuery = DB::table('employee_daily_reports as dr')
                ->join('employees as e', function ($join) use ($validNiks) {
                    $join->on('dr.employee_id', '=', 'e.NIK')
                        ->whereColumn('dr.employee_name', 'e.Nama')
                        ->whereColumn('dr.departement_id', 'e.Dept')
                        ->whereIn('e.NIK', $validNiks);
                })
                ->select('dr.*');

            // 4. Prepare dropdown of employees from filtered reports
            $employeesDropdown = (clone $reportQuery)
                ->select('employee_id', DB::raw('MIN(employee_name) as employee_name'))
                ->groupBy('employee_id')
                ->get();

            // 5. Apply filter from request
            $filterEmployeeId = $request->input('filter_employee_id');
            if ($filterEmployeeId && in_array($filterEmployeeId, $validNiks)) {
                $reportQuery->where('dr.employee_id', $filterEmployeeId);
            }

            $filterDepartmentNo = $request->input('filter_department_no');
            if ($filterDepartmentNo) {
                $reportQuery->where('dr.departement_id', $filterDepartmentNo);
            }

            // 6. Fetch and enrich employee records with latest work date & time
            $filteredReports = $reportQuery
                ->select('employee_id', 'departement_id', DB::raw('MIN(employee_name) as employee_name'))
                ->groupBy('employee_id', 'departement_id')
                ->get();

            $employees = $filteredReports->map(function ($employee) {
                $latest = EmployeeDailyReport::where('employee_id', $employee->employee_id)
                    ->orderByDesc('work_date')
                    ->orderByDesc('work_time')
                    ->first();

                $employee->latest_work_date = $latest->work_date ?? '-';
                $employee->latest_work_time = $latest->work_time ?? '-';

                return $employee;
            });

            $departmentNos = Department::pluck('dept_no');
            return view('dailyreport.depthead_index', compact('employees', 'employeesDropdown', 'departmentNos', 'filterEmployeeId', 'filterDepartmentNo'));
        } else {
            abort(403, 'Anda tidak memiliki akses');
        }
    }


    public function showDepthead(Request $request, $employee_id)
    {
        $user = auth()->user();

        if ($user->is_head || $user->specification->name === 'DIRECTOR') {
            $query = EmployeeDailyReport::where('employee_id', $employee_id);

            if ($user->name === 'Bernadett' || $user->specification->name === 'DIRECTOR') {
            } else {
                $query->where('departement_id', $user->department->dept_no);
            }

            $filter_start_date = $request->input('filter_start_date');
            $filter_end_date = $request->input('filter_end_date');

            if ($filter_start_date && $filter_end_date) {
                $query->whereBetween('work_date', [$filter_start_date, $filter_end_date]);
            } elseif ($filter_start_date) {
                $query->whereDate('work_date', '>=', $filter_start_date);
            } elseif ($filter_end_date) {
                $query->whereDate('work_date', '<=', $filter_end_date);
            }

            $reports = $query->orderByDesc('work_date')
                ->orderByDesc('work_time')
                ->get();

            $startDate = Carbon::parse($reports->min('work_date') ?? now()->subDays(30));
            $endDate = Carbon::parse($reports->max('work_date') ?? now());
            $allDates = collect(CarbonPeriod::create($startDate, $endDate))->map(fn($date) => $date->toDateString());

            $submittedDates = $reports->pluck('work_date')->map(fn($date) => \Carbon\Carbon::parse($date)->toDateString())->unique();
            $missingDates = $allDates->diff($submittedDates);

            // dd($submittedDates, $missingDates, $filter_start_date, $filter_end_date);
            $calendarEvents = [];

            // Submitted reports: green
            foreach ($submittedDates as $date) {
                $calendarEvents[] = [
                    'title' => '✔ Laporan Masuk',
                    'start' => $date,
                    'color' => '#28a745' // green
                ];
            }

            // Missing reports: red
            foreach ($missingDates as $date) {
                $calendarEvents[] = [
                    'title' => '❌ Tidak Ada Laporan',
                    'start' => $date,
                    'color' => '#dc3545' // red
                ];
            }

            // dd($missingDates);

            return view('dailyreport.depthead_show', compact(
                'reports',
                'employee_id',
                'filter_start_date',
                'filter_end_date',
                'missingDates',
                'submittedDates',
                'startDate',
                'endDate',
                'calendarEvents'
            ));
        } else {
            abort(403, 'Akses ditolak');
        }
    }
}
