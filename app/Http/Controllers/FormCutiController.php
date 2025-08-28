<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\FormCuti;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FormCutiController extends Controller
{
    public function index()
    {
        $formcuti = FormCuti::get();
        return view("formcuti.index", compact("formcuti"));
    }

    public function create()
    {
        $deparments = Department::all();
        return view("formcuti.create", compact("deparments"));
    }

    public function store(Request $request)
    {
        // Get the values from the request
        $waktuCuti = $request->input("waktu_cuti");
        $satuanWaktuCuti = $request->input("satuan_waktu_cuti");

        // Concatenate the values
        $waktuCuti .= " " . $satuanWaktuCuti;
        // dd($request->all());

        $formcuti = FormCuti::create([
            "name" => $request->input("name"),
            "jabatan" => $request->input("jabatan"),
            "department" => $request->input("department"),
            "jenis_cuti" => $request->input("jenis_cuti"),
            "pengganti" => $request->input("pengganti"),
            "keperluan" => $request->input("keperluan"),
            "tanggal_masuk" => $request->input("tanggal_masuk"),
            "no_karyawan" => $request->input("no_karyawan"),
            "tanggal_permohonan" => $request->input("tanggal_permohonan"),
            "mulai_tanggal" => $request->input("mulai_tanggal"),
            "sampai_tanggal" => $request->input("sampai_tanggal"),
            "keterangan_user" => $request->input("keterangan_user"),
            "waktu_cuti" => $waktuCuti,
            "is_accept" => false,
        ]);

        return redirect()
            ->route("formcuti.home")
            ->with("success", "form cuti created successfully");
    }

    public function detail($id)
    {
        $formcuti = FormCuti::find($id);
        $user = Auth::user();

        return view("formcuti.detail", compact("user", "formcuti"));
    }

    public function saveImagePath(Request $request, $formId, $section)
    {
        $username = Auth::check() ? Auth::user()->name : "";
        $imagePath = $username . ".png";

        // Save $imagePath to the database for the specified $reportId and $section
        $fc = FormCuti::find($formId);
        $fc->update([
            "autograph_{$section}" => $imagePath,
        ]);
        $fc->update([
            "autograph_user_{$section}" => $username,
            "is_accept" => true,
        ]);

        return response()->json(["success" => "Autograph saved successfully!"]);
    }
}
