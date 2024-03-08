<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\Lapor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LaporController extends Controller
{
     public function index ()
    {
        return view('formlapor.index');
       
    }

    }


