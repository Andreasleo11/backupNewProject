<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuratPerintahKerjaKomputer;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Str;


class SuratPerintahKerjaKomputerController extends Controller
{
    public function index()
    {
        $reports = SuratPerintahKerjaKomputer::all();
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
}
