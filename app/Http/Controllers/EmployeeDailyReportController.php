<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 100000);

use App\Models\Employee;
use App\Models\EmployeeDailyReport;
use App\Models\EmployeeDailyReportLog;
use Carbon\Carbon;
use Carbon\CarbonPeriod; // Kalau pakai Excel
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class EmployeeDailyReportController extends Controller
{
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

        if (! $employee) {
            return back()->withErrors(['nik' => 'NIK tidak ditemukan.']);
        }

        // Generate expected password: NIK + ddmmyyyy
        $expectedPassword = $employee->NIK.$employee->date_birth->format('dmY');
        
        if ($request->password === $expectedPassword) {
            // Simpan info login manual ke session
            Session::put('employee_id', $employee->id);
            Session::put('employee_nik', $employee->NIK);
            
            return redirect()->route('daily-reports.index')->with('success', 'Login berhasil!');
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
                if ($originalKey === null) {
                    continue;
                }

                $key = trim($originalKey);
                // Tambahkan suffix jika nama kolom sudah dipakai
                if (isset($usedColumns[$key])) {
                    $suffix = ++$usedColumns[$key];
                    $key .= '.'.$suffix;
                } else {
                    $usedColumns[$key] = 0;
                }

                $record[$key] = $value;
            }

            if (! isset($record['ID Karyawan (8 Digit) (Contoh: 39001234)'])) {
                continue;
            }

            $employeeIdRaw = trim($record['ID Karyawan (8 Digit) (Contoh: 39001234)']);
            $normalizedId = str_replace([' ', '-'], '', $employeeIdRaw);
            if (! preg_match('/^\d{8}$/', $normalizedId)) {
                continue;
            }

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
                    ? Carbon::instance(
                        Date::excelToDateTimeObject($record['Tanggal Bekerja']),
                    )->toDateString()
                    : Carbon::parse($record['Tanggal Bekerja'])->toDateString();
            } catch (\Exception $e) {
                continue;
            }

            for ($i = 1; $i <= 12; $i++) {
                $jamKey = "Jam Sesi $i";
                $descKey =
                    $i === 1
                        ? 'Deskripsi Pekerjaan yang dilakukan'
                        : 'Deskripsi Pekerjaan yang dilakukan.'.($i - 1);
                $proofKey = $i === 1 ? 'Bukti Pekerjaan' : 'Bukti Pekerjaan.'.($i - 1);

                if (empty($record[$jamKey]) || empty($record[$descKey])) {
                    continue;
                }

                $previewData[] = [
                    'submitted_at' => $submittedAt->format('Y-m-d H:i:s'),
                    'report_type' => 'Baru',
                    'employee_id' => $employeeId,
                    'employee_name' => $employeeName,
                    'departement_id' => $employeeDept,
                    'work_date' => $workDate,
                    'work_time' => trim($record[$jamKey]),
                    'work_description' => trim($record[$descKey]),
                    'proof_url' => $record[$proofKey] ?? null,
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

    public function showDepthead(Request $request, $employee_id)
    {
        $user = auth()->user();

        if (true) {
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

            $reports = $query->orderByDesc('work_date')->orderByDesc('work_time')->get();

            $startDate = Carbon::parse($reports->min('work_date') ?? now()->subDays(30));
            $endDate = now()->subDay();
            $allDates = collect(CarbonPeriod::create($startDate, $endDate))->map(
                fn ($date) => $date->toDateString(),
            );

            $submittedDates = $reports
                ->pluck('work_date')
                ->map(fn ($date) => \Carbon\Carbon::parse($date)->toDateString())
                ->unique();
            $missingDates = $allDates->diff($submittedDates);

            // dd($submittedDates, $missingDates, $filter_start_date, $filter_end_date);
            $calendarEvents = [];

            // Submitted reports: green
            foreach ($submittedDates as $date) {
                $calendarEvents[] = [
                    'title' => '✔ Laporan Masuk',
                    'start' => $date,
                    'color' => '#28a745', // green
                ];
            }

            // Missing reports: red
            foreach ($missingDates as $date) {
                $calendarEvents[] = [
                    'title' => '❌ Tidak Ada Laporan',
                    'start' => $date,
                    'color' => '#dc3545', // red
                ];
            }

            // dd($missingDates);

            return view(
                'dailyreport.depthead_show',
                compact(
                    'reports',
                    'employee_id',
                    'filter_start_date',
                    'filter_end_date',
                    'missingDates',
                    'submittedDates',
                    'startDate',
                    'endDate',
                    'calendarEvents',
                ),
            );
        } else {
            abort(403, 'Akses ditolak');
        }
    }
}
