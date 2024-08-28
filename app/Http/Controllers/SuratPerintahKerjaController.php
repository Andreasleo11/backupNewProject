<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSuratPerintahKerjaRequest;
use Illuminate\Http\Request;
use App\Models\SuratPerintahKerja;
use App\Models\User;
use App\Models\Department;
use App\Models\File;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use DateTime;
use Illuminate\Support\Facades\Storage;

class SuratPerintahKerjaController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->user();

        $reportsQuery = SuratPerintahKerja::with('fromDepartment', 'createdBy');

        if ($authUser->department->name !== 'COMPUTER') {
            if ($authUser->department->name === 'PERSONALIA' || $authUser->department->name === 'MAINTENANCE') {
                // Show all records where to_department matches the user's department
                $reportsQuery = SuratPerintahKerja::whereHas('fromDepartment', function ($query) use ($authUser) {
                    $query->where('to_department', $authUser->department->name);
                });
            } else {
                // For other departments, show records where fromDepartment or pelapor matches
                $reportsQuery = SuratPerintahKerja::whereHas('fromDepartment', function ($query) use ($authUser) {
                    $query->where('id', $authUser->department->id);
                })->orWhere('pelapor', $authUser->name);
            }
        }

        // Check if filter parameters are set
        if ($request->has(['filter_column', 'filter_action', 'filter_value'])) {
            $filterColumn = $request->input('filter_column');
            $filterAction = $request->input('filter_action');
            $filterValue = $request->input('filter_value');

            // Validate filter column
            $validColumns = ['no_dokumen', 'pelapor', 'tanggal_lapor', 'judul_laporan', 'pic'];
            if (in_array($filterColumn, $validColumns)) {
                switch ($filterAction) {
                    case 'contains':
                        $reportsQuery->where($filterColumn, 'like', '%' . $filterValue . '%');
                        break;
                    case 'equals':
                        $reportsQuery->where($filterColumn, '=', $filterValue);
                        break;
                    case 'between':
                        if ($filterColumn === 'tanggal_lapor' && $request->has('filter_value_2')) {
                            $filterValue2 = $request->input('filter_value_2');
                            $reportsQuery->whereBetween($filterColumn, [$filterValue, $filterValue2]);
                        }
                        break;
                    case 'greater_than':
                        if ($filterColumn === 'tanggal_lapor') {
                            $reportsQuery->where($filterColumn, '>', $filterValue);
                        }
                        break;
                    case 'less_than':
                        if ($filterColumn === 'tanggal_lapor') {
                            $reportsQuery->where($filterColumn, '<', $filterValue);
                        }
                        break;
                }
            }
        }

        $reports = $reportsQuery
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->appends($request->except('page'));

        return view('spk.index', compact('reports'));
    }

    public function createpage()
    {
        $departments = Department::all();
        $username = auth()->user()->name;

        return view('spk.create', compact('departments', 'username'));
    }

    public function inputprocess(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'no_dokumen' => 'required|string|max:255',
            'pelapor' => 'required|string|max:255',
            'tanggallapor' => 'required|date',
            'from_department' => 'required|string|max:255',
            'judul_laporan' => 'required|string|max:255',
            'keterangan_laporan' => 'required|string',
            'to_department' => 'required|string',
            'requested_by' => 'required|string',
            'attachments.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'type' => 'nullable|string|max:255',
            'part_no' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'machine' => 'nullable|string|max:255',
            'is_urgent' => 'required|in:yes,no',
            'for' => 'nullable|string|max:255|in:machine,mould'
        ]);

        if ($validatedData['to_department'] === 'MAINTENANCE MOULDING') {
            $request->validate(['for' => 'required']);
        }

        // dd($validatedData);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                // $filePath = $file->storeAs('files', $filename, 'public');
                $fileType = $file->getMimeType();
                $fileSize = $file->getSize();
                $file->storeAs('public/files', $filename);
                File::create([
                    'doc_id' => $request->no_dokumen,
                    'name' => $filename,
                    'mime_type' => $fileType,
                    'size' => $fileSize,
                ]);
            }
        }

        if (isset($validatedData['tanggallapor'])) {
            $validatedData['tanggallapor'] = str_replace('T', ' ', $validatedData['tanggallapor']);
        }

        $spk = new SuratPerintahKerja();
        $spk->no_dokumen = $validatedData['no_dokumen'];
        $spk->pelapor = $validatedData['pelapor'];
        $spk->tanggal_lapor = $validatedData['tanggallapor'];
        $spk->from_department = $validatedData['from_department'];
        $spk->to_department = $validatedData['to_department'];
        $spk->judul_laporan = $validatedData['judul_laporan'];
        $spk->keterangan_laporan = $validatedData['keterangan_laporan'];
        $spk->requested_by = $validatedData['requested_by'];
        $spk->status_laporan = 0;
        $spk->is_urgent = $validatedData['is_urgent'] === 'yes' ? true : false;

        $spk->save();

        return redirect()->route('spk.index')->with('success', 'Data successfully inserted.');
    }

    public function detail($id)
    {
        $report = SuratPerintahKerja::with('spkRemarks')->find($id);
        $files = File::where('doc_id', $report->no_dokumen)->get();

        $users = null;
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

        $fromDepartment = $report->fromDepartment;
        $depthead = User::whereHas('department', function ($query) use ($fromDepartment) {
            $query->where('name', $fromDepartment);
        })->where('is_head', true)
            ->first();

        return view('spk.detail', compact('report', 'users', 'depthead', 'files'));
    }

    public function update(UpdateSuratPerintahKerjaRequest $request, $id)
    {
        $report = SuratPerintahKerja::findOrFail($id);
        $validated = $request->validated();

        $validated['status_laporan'] = $this->determineStatus($report, $validated);

        $report->update($validated);

        // Redirect back with success message
        return redirect()->back()->with('success', 'SPK updated successfully!');
    }

    public function saveAutograph(Request $request, $id)
    {
        $report = SuratPerintahKerja::findOrFail($id);

        $data = $request->all();
        $data['status_laporan'] = $this->determineStatus($report, $data);

        $report->update($data);

        return redirect()->back()->with('success', 'SPK successfully approved!');
    }

    private function determineStatus($report, $data)
    {
        if ((!empty($data['tanggal_selesai']) || $report->tanggal_selesai) && $report->pic_autograph) {
            return 4;
        } elseif ((!empty($data['pic']) || $report->pic) && (!empty($data['tindakan']) || $report->tindakan) && (!empty($data['tanggal_mulai']) || $report->tanggal_mulai) && (!empty($data['tanggal_estimasi']) || $report->tanggal_estimasi) && $report->admin_autograph) {
            return 3;
        } elseif ($report->to_department === 'MAINTENANCE MOULDING' && (($report->dept_head_autograph || !empty($data['dept_head_autograph'])) && $report->creator_autograph || $report->is_urgent && $report->creator_autograph)) {
            return 6;
        } elseif (!empty($data['dept_head_autograph'])) {
            return 2;
        } elseif (!empty($data['creator_autograph']) || $report->cretor_autograph) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $report = SuratPerintahKerja::findOrFail($id);
            $report->delete();

            return redirect()->back()->with('success', 'SPK deleted successfully!');
        } catch (Exception $e) {
            // Log the exception message for debugging
            Log::error('Failed to delete SPK: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to delete SPK. Please try again later.');
        }
    }

    public function monthlyreport(Request $request)
    {
        // Fetch all SuratPerintahKerja records
        // Get current month and year from request or set default
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Fetch all SuratPerintahKerja records filtered by month and year
        $reports = SuratPerintahKerja::whereYear('tanggal_lapor', $year)
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
            $dateMulai = new DateTime($report->tanggal_mulai);
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
                'from_department' => $report->from_department,
                'judul' => $report->judul_laporan,
                'keterangan_laporan' => $report->keterangan_laporan,
                'pic' => $report->pic,
                'tindakan' => $report->tindakan,
                'tanggal_lapor' => $report->tanggal_lapor,
                'tanggal_mulai' => $report->tanggal_mulai,
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
            'monthlyReport' => $monthlyReport,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function revision(Request $request, $id)
    {
        $validated = $request->validate(['revision_reason' => 'required|string|max:255']);

        $report = SuratPerintahKerja::find($id);
        $report->update([
            'status_laporan' => 3,
            'is_revision' => true,
            'revision_count' => $report->revision_count + 1,
            'pic_autograph' => null,
            'approved_autograph' => null,
            'tanggal_selesai' => null,
            'tindakan' => null,
            'revision_reason' => $validated['revision_reason']
        ]);

        return redirect()->back()->with('success', 'Ask a revision requested!');
    }

    public function finish($id)
    {
        SuratPerintahKerja::find($id)->update(['status_laporan' => 5]);
        return redirect()->back()->with('success', 'Spk finished!');
    }
}
