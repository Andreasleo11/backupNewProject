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
        return view('view');
    }

    public function createpage()
    {
        $username = auth()->user()->name;
        $department = Department::all();

        return view('view', compact('username', 'department'));
    }
}
