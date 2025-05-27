<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

            $workDate = Carbon::createFromFormat('d/m/Y', trim($data['Tanggal melakukan pekerjaan']))->toDateString();

            $reportData = [
                'submitted_at'        => Carbon::createFromFormat('d/m/Y H:i:s', $data['Timestamp']),
                'report_type'         => $data['Jenis Laporan'],
                'employee_id'         => $employeeId,
                'employee_name'       => $data['Nama'],
                'departement_id'      => $department,
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

}
