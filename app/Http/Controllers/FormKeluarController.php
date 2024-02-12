<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FormKeluar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FormKeluarController extends Controller
{
    public function index ()
    {
        return view ('formkeluar.index');
    }

    public function create()
    {
        return view('formkeluar.create');
    }

    public function store(Request $request)
   {
    // Get the values from the request
    $waktu_keluar = $request->input('waktu_keluar');
    $satuan_waktu_keluar = $request->input('satuan_waktu_keluar');

    // Concatenate the values
    $waktu_keluar .= ' ' . $satuan_waktu_keluar;
    // dd($request->all());

        $formcuti = FormKeluar::create([
            'name' =>  $request->input('name'),
            'jabatan' => $request->input('jabatan'),
            'department' =>  $request->input('department'), 
            'alasan_izin_keluar' =>  $request->input('alasan_izin_keluar'),
            'pengganti' =>  $request->input('pengganti'),
            'keperluan' =>  $request->input('keperluan'),
            'tanggal_masuk' =>  $request->input('tanggal_masuk'),
            'no_karyawan' =>  $request->input('no_karyawan'),
            'tanggal_permohonan' =>  $request->input('tanggal_permohonan'),
            'keterangan_user' =>  $request->input('keterangan_user'),
            'waktu_keluar' => $waktu_keluar,
            'jam_keluar' =>  $request->input('jam_keluar'),
            'jam_kembali' =>  $request->input('jam_kembali'),
        ]);

        return redirect()->route('formcuti.home')->with('success', 'form cuti created successfully');
   }
}
