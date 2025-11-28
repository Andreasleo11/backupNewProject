<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\FormKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormKeluarController extends Controller
{
    public function index()
    {
        $formkeluar = FormKeluar::get();

        return view('formkeluar.index', compact('formkeluar'));
    }

    public function create()
    {
        $departments = Department::all();

        return view('formkeluar.create', compact('departments'));
    }

    public function store(Request $request)
    {
        // Get the values from the request
        $waktu_keluar = $request->input('waktu_keluar');
        $satuan_waktu_keluar = $request->input('satuan_waktu_keluar');

        // Concatenate the values
        $waktu_keluar .= ' '.$satuan_waktu_keluar;
        // dd($request->all());

        $formcuti = FormKeluar::create([
            'name' => $request->input('name'),
            'jabatan' => $request->input('jabatan'),
            'department' => $request->input('department'),
            'alasan_izin_keluar' => $request->input('alasan_izin_keluar'),
            'pengganti' => $request->input('pengganti'),
            'keperluan' => $request->input('keperluan'),
            'tanggal_masuk' => $request->input('tanggal_masuk'),
            'no_karyawan' => $request->input('no_karyawan'),
            'tanggal_permohonan' => $request->input('tanggal_permohonan'),
            'keterangan_user' => $request->input('keterangan_user'),
            'waktu_keluar' => $waktu_keluar,
            'jam_keluar' => $request->input('jam_keluar'),
            'jam_kembali' => $request->input('jam_kembali'),
        ]);

        return redirect()
            ->route('formkeluar')
            ->with('success', 'form keluar created successfully');
    }

    public function detail($id)
    {
        $formkeluar = FormKeluar::find($id);
        $user = Auth::user();

        return view('formkeluar.detail', compact('formkeluar', 'user'));
    }

    public function saveImagePath(Request $request, $formId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : '';
        $imagePath = $username.'.png';

        // Save $imagePath to the database for the specified $reportId and $section
        $fc = FormKeluar::find($formId);
        $fc->update([
            "autograph_{$section}" => $imagePath,
        ]);
        $fc->update([
            "autograph_user_{$section}" => $username,
            'is_accept' => true,
        ]);

        return response()->json(['success' => 'Autograph saved successfully!']);
    }
}
