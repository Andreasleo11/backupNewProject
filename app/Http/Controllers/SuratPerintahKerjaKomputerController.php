<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuratPerintahKerjaKomputer;
use App\Models\User;
use App\Models\Department;


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

        return view('spk.create', compact('departments'));
    }
}
