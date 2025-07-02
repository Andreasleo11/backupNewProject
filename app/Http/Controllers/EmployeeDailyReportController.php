<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 100000);

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\EmployeeDailyReport;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel; // Kalau pakai Excel
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Session;
use Carbon\CarbonPeriod;


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
        $data = Excel::toArray([], $file); // Mengembalikan array dari Excel
        $rows = $data[0]; // Sheet pertama

        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        $processedRows = [];

        foreach ($rows as $row) {
            // Cek kolom tanggal, misal index 4
            if (isset($row[0]) && is_numeric($row[0])) {
                $excelDateTime = $row[0];
                $unixTimestamp = ($excelDateTime - 25569) * 86400;
                $row[0] = gmdate("d/m/Y H:i:s", $unixTimestamp);
            }

            if (isset($row[4]) && is_numeric($row[4])) {
                $excelDate = $row[4];
                $unixTimestamp = ($excelDate - 25569) * 86400;
                $row[4] = gmdate("d/m/Y", $unixTimestamp);
            }
            $processedRows[] = $row;
        }


        foreach ($processedRows as $row) {
            $data = array_combine($header, $row);

            $employeeIdRaw = trim($data['ID Karyawan (Cth: 39001234)']);

            // Hilangkan semua spasi dan strip dulu
            $normalizedId = str_replace([' ', '-'], '', $employeeIdRaw);

            // Cek apakah sudah tepat 8 digit angka
            if (!preg_match('/^\d{8}$/', $normalizedId)) {
                // Skip kalau format tidak valid
                continue;
            }

            // Pecah jadi department dan employee id
            $department = substr($normalizedId, 0, 3); // 3 karakter awal
            $employeeId = substr($normalizedId, 3);    // 5 karakter akhir

            $employee = Employee::where('NIK', $employeeId)->first();
            if ($employee) {
                $employeeName = $employee->Nama;
                $employeeDept = $employee->Dept;
            } else {
                $employeeName = 'Unknown';
                $employeeDept = 'Unknown';
            }


            $workDate = Carbon::createFromFormat('d/m/Y', trim($data['Tanggal melakukan pekerjaan']))->toDateString();

            $reportData = [
                'submitted_at'        => Carbon::createFromFormat('d/m/Y H:i:s', $data['Timestamp']),
                'report_type'         => $data['Jenis Laporan'],
                'employee_id'         => $employeeId,
                'employee_name'       => $employeeName,
                'departement_id'      => $employeeDept,
                'work_date'           => $workDate,
                'work_time'           => $data['Jam bekerja'],
                'work_description'    => $data['Deskripsi pekerjaan yang dilakukan'],
                'proof_url'           => $data['Bukti kegiatan pekerjaan'] ?? null,
            ];

            // Cek duplikat penuh
            $isDuplicate = EmployeeDailyReport::where($reportData)->exists();
            if ($isDuplicate) continue;

            // Cek report_type & jam bekerja
            $existingSameTime = EmployeeDailyReport::where('employee_id', $employeeId)
                ->where('work_date', $reportData['work_date'])
                ->where('work_time', $reportData['work_time'])
                ->where('report_type', $reportData['report_type'])
                ->first();

            if ($existingSameTime && $reportData['report_type'] === 'Baru') {
                continue; // Skip kalau sama2 Baru
            }

            if ($existingSameTime && $reportData['report_type'] === 'Revisi') {
                // Update data yang lama
                // dd($reportData);
                $existingSameTime->update($reportData);
                continue;
            }

            // Insert baru
            EmployeeDailyReport::create($reportData);
        }

        return redirect()->route('daily-report.form')->with('success', 'Laporan berhasil di-upload!');
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
