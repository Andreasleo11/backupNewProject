<?php

namespace App\Http\Controllers\pps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PPSKarawangController extends Controller
{
    public function index()
    {
        return view("pps.karawangindex");
    }
}