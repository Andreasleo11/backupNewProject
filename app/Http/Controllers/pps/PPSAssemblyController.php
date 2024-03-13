<?php

namespace App\Http\Controllers\pps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PPSAssemblyController extends Controller
{
    public function indexscenario()
    {
        return view("pps.assemblyindex");
    }

    public function deliveryassembly()
    {
        return view("pps.assemblydelivery");
    }

    public function itemassembly()
    {
        return view("pps.assemblyitem");
    }

    public function lineassembly()
    {
        return view("pps.assemblyline");
    }

    public function finalresultassembly()
    {
        return view("pps.finalresultppsassembly");
    }
}
