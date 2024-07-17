<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSuratPerintahKerjaKomputerRequest;
use Illuminate\Http\Request;
use App\Models\SuratPerintahKerjaKomputer;
use App\Models\User;
use App\Models\Department;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class SuratPerintahKerjaKomputerController extends Controller
{
    public function index()
    {

        $this->updatestatus();

        $authUser = auth()->user();

        $reportsQuery = SuratPerintahKerjaKomputer::with('deptRelation', 'createdBy');
        // dd($reportsQuery->get());

        if ($authUser->department->name !== 'COMPUTER') {
            $reportsQuery = SuratPerintahKerjaKomputer::whereHas('deptRelation', function ($query) use ($authUser) {
                $query->where('id', $authUser->department->id);
            });

            $reportsQuery->orWhere('pelapor', $authUser->name);
        }

        $reports = $reportsQuery
            ->orderBy('created_at', 'desc')
            ->get();

        return view('spk.index', compact('reports'));
    }

    public function createpage()
    {
        $departments = Department::all();
        $username = auth()->user()->name;

        $randomString = Str::random(5);

        $docnum = 'SPK-' . $randomString;

        return view('spk.create', compact('departments', 'username', 'docnum'));
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
        ]);

        // Create a new instance of SuratPerintahKerjaKomputer and populate it with the validated data
        $spk = new SuratPerintahKerjaKomputer();
        $spk->no_dokumen = $validatedData['no_dokumen'];
        $spk->pelapor = $validatedData['pelapor'];
        $spk->tanggal_lapor = $validatedData['tanggallapor'];
        $spk->dept = $validatedData['dept'];
        $spk->judul_laporan = $validatedData['judul_laporan'];
        $spk->keterangan_laporan = $validatedData['keterangan_laporan'];

        // Save the instance to the database
        $spk->save();

        // Optionally, you can return a response or redirect
        return redirect()->route('spk.index')->with('success', 'Data successfully inserted.');
    }

    public function detail($id)
    {
        $this->updatestatus();
        $report = SuratPerintahKerjaKomputer::find($id);
        return view('spk.detail', compact('report'));
    }

    public function update(UpdateSuratPerintahKerjaKomputerRequest $request, $id)
    {
        // The request is already validated at this point.

        // Find the record to update
        $report = SuratPerintahKerjaKomputer::findOrFail($id);

        // Update the record with validated data
        $report->update($request->validated());

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
            // Initialize status_laporan as 0
            $report->status_laporan = 0;

            // Check if tanggal_selesai is not null
            if ($report->tanggal_selesai !== null) {
                $report->status_laporan = 2;
            }
            // Check if pic, keterangan_pic, and tanggal_estimasi are not null
            elseif ($report->pic !== null && $report->keterangan_pic !== null && $report->tanggal_estimasi !== null) {
                $report->status_laporan = 1;
            }

            // Save the updated report
            $report->save();
        }
        return;
    }
}
