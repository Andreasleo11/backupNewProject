<?php

namespace App\Http\Controllers\pps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PPSGeneralController extends Controller
{
    public function index()
    {

        return view("pps.index");
    }

    public function menu()
    {
        // example - harus diganti ambil data dari model 
        $inj = "Plastic Injection";
        $sec = "Second Process";
        $ass = "Assembly";

          // example - harus diganti ambil data dari model 
        return view("pps.menu", compact("inj", "sec", "ass"));
    }


    public function portal(Request $request)
    {
        $scenario = $request->input('scenario');

        if ($scenario === 'injection') {
            return redirect()->route('indexinjection');
        } elseif ($scenario === 'second') {
            return redirect()->route('indexsecond');
        } elseif ($scenario === 'assembly') {
            return redirect()->route('indexassembly');
        }
    
        // Default redirection if scenario is not recognized
        return redirect()->route('indexpps');
    }
    
}
