<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSuratPerintahKerjaKomputerRequest;
use Illuminate\Http\Request;
use App\Models\SuratPerintahKerjaKomputer;
use App\Models\User;
use App\Models\SpkRemark;
use App\Models\Department;
use App\Notifications\SPKCreated;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use DateTime;
use Illuminate\Support\Facades\Notification;

class SuratPerintahKerjaKomputerController extends Controller
{
    public function index()
    {
        $this->updatestatus();

        $authUser = auth()->user();

        $reportsQuery = SuratPerintahKerjaKomputer::with('deptRelation', 'createdBy');
        

        if ($authUser->department->name === 'COMPUTER' || $authUser->department->name === 'PERSONALIA' || $authUser->department->name === 'MAINTENANCE') {
            // Show all records where to_department matches the user's department
            $reportsQuery = SuratPerintahKerjaKomputer::whereHas('deptRelation', function ($query) use ($authUser) {
                $query->where('to_department', $authUser->department->name);
            });
        } else {
            // For other departments, show records where deptRelation or pelapor matches
            $reportsQuery = SuratPerintahKerjaKomputer::whereHas('deptRelation', function ($query) use ($authUser) {
                $query->where('id', $authUser->department->id);
            })->orWhere('pelapor', $authUser->name);
        }

        $reports = $reportsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        

        return view('spk.index', compact('reports'));
    }

    public function createpage()
    {
        $departments = Department::all();
        $username = auth()->user()->name;

        $randomString = Str::random(5);

        $docnum = 'DI/SPK';

        return view('spk.create', compact('departments', 'username', 'docnum'));
    }

    function generateNoDokumen($department)
    {
        $prefix = 'DI';
        $randomNumber = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT); // Generates a 5-character random number

        switch ($department) {
            case 'COMPUTER':
                $middle = 'CP';
                break;
            case 'HRD':
                $middle = 'HRD';
                break;
            case 'MAINTENANCE':
                $middle = 'MT';
                break;
            default:
                $middle = 'UNKNOWN';
                break;
        }

        return "$prefix/$middle/SPK/$randomNumber";
    }

    public function inputprocess(Request $request)
    {

        // Validate the request data
        $validatedData = $request->validate([
            'no_dokumen' => 'required|string|max:255',
            'pelapor' => 'required|string|max:255',
            'tanggallapor' => 'required|date',
            'dept' => 'required|string|max:255',
            'judul_laporan' => 'required|string|max:255',
            'keterangan_laporan' => 'required|string',
            'to_department' => 'required|string',
        ]);

        if($validatedData['to_department'] === 'COMPUTER')
        {
            $validatedData['no_dokumen'] = $this->generateNoDokumen('COMPUTER');
        }
        elseif($validatedData['to_department'] === 'PERSONALIA')
        {
            $validatedData['no_dokumen'] = $this->generateNoDokumen('HRD');
        }
        elseif($validatedData['to_department'] === 'MAINTENANCE')
        {
            $validatedData['no_dokumen'] = $this->generateNoDokumen('MAINTENANCE');
        }

        // dd($validatedData['no_dokumen']);
        // Replace the 'T' with a space in tanggallapor
        if (isset($validatedData['tanggallapor'])) {
            $validatedData['tanggallapor'] = str_replace('T', ' ', $validatedData['tanggallapor']);
        }

        // Create a new instance of SuratPerintahKerjaKomputer and populate it with the validated data
        $spk = new SuratPerintahKerjaKomputer();
        $spk->no_dokumen = $validatedData['no_dokumen'];
        $spk->pelapor = $validatedData['pelapor'];
        $spk->tanggal_lapor = $validatedData['tanggallapor'];
        $spk->dept = $validatedData['dept'];
        $spk->to_department = $validatedData['to_department'];
        $spk->judul_laporan = $validatedData['judul_laporan'];
        $spk->keterangan_laporan = $validatedData['keterangan_laporan'];
        $spk->status_laporan = 0;

        // Save the instance to the database
        $spk->save();

        // Optionally, you can return a response or redirect
        return redirect()->route('spk.index')->with('success', 'Data successfully inserted.');
    }

    public function detail($id)
    {
        
        $this->updatestatus();
        $report = SuratPerintahKerjaKomputer::with('spkRemarks')->find($id);
        // dd($report);

        $users = null; // Initialize the $users variable

        switch ($report->to_department) {
            case 'COMPUTER':
                $users = User::where('department_id', 15)->get();
                break;
            case 'MAINTENANCE':
                $users = User::where('department_id', 18)->get();
                break;
            case 'HRD':
                $users = User::where('department_id', 22)->get();
                break;
            default:
                // Handle other departments if needed
                $users = collect(); // Empty collection if no match found
                break;
        }

        // dd($users);


        $dept = $report->dept;
        $depthead = User::whereHas('department', function ($query) use ($dept) {
            $query->where('name', $dept);
        })->where('is_head', true)
            ->first();

        return view('spk.detail', compact('report', 'users', 'depthead'));
    }

    public function update(UpdateSuratPerintahKerjaKomputerRequest $request, $id)
    {
        // The request is already validated at this point.
        // dd($request->all());
        // Find the record to update
        $report = SuratPerintahKerjaKomputer::findOrFail($id);
        // Update the record with validated data
        $report->update($request->validated());
        if ($report->tanggal_selesai !== null) {
            $report->status_laporan = 2;
        }
        // Check if pic, keterangan_pic, and tanggal_estimasi are not null
        elseif ($report->pic !== null && $report->keterangan_pic !== null && $report->tanggal_estimasi !== null && $report->tanggal_terima !== null) {
            $report->status_laporan = 1;
        }

        // Save the updated report
        $report->save();
        $remarks = $request->keterangan_pic;
        $status = $report->status_laporan;
        $reportid = $report->id;
        // dd($status);
        SpkRemark::create([
            'spk_id' => $reportid,
            'status' => $status,
            'remarks' => $remarks,
        ]);

        // Redirect back with success message
        return redirect()->back()->with('success', 'SPK updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $report = SuratPerintahKerjaKomputer::findOrFail($id);
            $report->delete();

            return redirect()->back()->with('success', 'SPK deleted successfully!');
        } catch (Exception $e) {
            // Log the exception message for debugging
            Log::error('Failed to delete SPK: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to delete SPK. Please try again later.');
        }
    }

    public function updatestatus()
    {
        $reports = SuratPerintahKerjaKomputer::all();

        foreach ($reports as $report) {
            // // Initialize status_laporan as 0
            // $report->status_laporan = 0;

            // Check if tanggal_selesai is not null
            if ($report->tanggal_selesai !== null) {
                $report->status_laporan = 2;
            }
            // Check if pic, keterangan_pic, and tanggal_estimasi are not null
            elseif ($report->pic !== null && $report->keterangan_pic !== null && $report->tanggal_estimasi !== null && $report->tanggal_terima !== null) {
                $report->status_laporan = 1;
            }

            // Save the updated report
            $report->save();
        }
    }

    public function monthlyreport(Request $request)
    {
        // Fetch all SuratPerintahKerjaKomputer records
        // Get current month and year from request or set default
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Fetch all SuratPerintahKerjaKomputer records filtered by month and year
        $reports = SuratPerintahKerjaKomputer::whereYear('tanggal_lapor', $year)
            ->whereMonth('tanggal_lapor', $month)
            ->get();

        // Initialize an array to store formatted report data
        $monthlyReport = [];

        // Process each report to extract required fields and calculate durations
        foreach ($reports as $report) {
            // Calculate duration between tanggal_selesai and tanggal_lapor
            $dateMulai = null;
            $dateEstimasi = null;
            $estimasiFormatted = '';
            $menitEstimasi = 0;
            $menitDurasi = 0;

            $dateLapor = new DateTime($report->tanggal_lapor);
            $durasiFormatted = '';

            if (!empty($report->tanggal_selesai)) {
                $dateSelesai = new DateTime($report->tanggal_selesai);
                $durasi = $dateLapor->diff($dateSelesai);
                $durasiFormatted = sprintf('%d hari, %d jam, %d menit', $durasi->days, $durasi->h, $durasi->i);
                $menitDurasi = $durasi->days * 24 * 60 + $durasi->h * 60 + $durasi->i;
            } else {
                // Handle case where tanggal_selesai is not filled
                $durasiFormatted = 'Belum selesai'; // Or any default message you prefer
            }

            // Calculate estimasi_kesepakatan based on your logic
            $dateMulai = new DateTime($report->tanggal_terima);
            $dateEstimasi = new DateTime($report->tanggal_estimasi);
            $estimasi = $dateMulai->diff($dateEstimasi);
            $estimasiFormatted = sprintf('%d hari, %d jam, %d menit', $estimasi->days, $estimasi->h, $estimasi->i);

            // Convert durations to minutes

            $menitEstimasi = $estimasi->days * 24 * 60 + $estimasi->h * 60 + $estimasi->i;


            $presentase = ($menitEstimasi !== 0 && $menitDurasi !== 0 && $menitDurasi !== 0)
                ? min(1, $menitEstimasi / $menitDurasi) * 100
                : 0;

            // Prepare the data for the monthly report
            $monthlyReport[] = [
                'no_dokumen' => $report->no_dokumen,
                'pelapor' => $report->pelapor,
                'dept' => $report->dept,
                'judul' => $report->judul_laporan,
                'keterangan_laporan' => $report->keterangan_laporan,
                'pic' => $report->pic,
                'keterangan_pic' => $report->keterangan_pic,
                'tanggal_lapor' => $report->tanggal_lapor,
                'tanggal_terima' => $report->tanggal_terima,
                'tanggal_selesai' => $report->tanggal_selesai,
                'durasi' => $durasiFormatted,
                'estimasi_kesepakatan' => $estimasiFormatted,
                'menit_estimasi' => $menitEstimasi,
                'menit_durasi' => $menitDurasi ?? 0,
                'presentase' => $presentase ?? 0,
            ];
        }

        // Output or return the formatted monthly report

        return view('spk.monthlyreport', [
            'monthlyReport' => $monthlyReport, 'month' => $month,
            'year' => $year,
        ]);
    }
}
