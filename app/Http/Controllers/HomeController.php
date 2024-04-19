<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = auth()->user();


        if ($user->role_id == 1) {
            return redirect()->route('superadmin.home');
        } else if ($user->role_id == 2){
            // dd($user->department->name);
            $department = $user->department->name;
            switch ($department) {
                case "QA":
                    return redirect()->route('qaqc.home');
                    break;
                case "QC":
                    return redirect()->route('qaqc.home');
                    break;
                case "ACCOUNTING":
                    return redirect()->route('accounting.home');
                    break;
                case "DIRECTOR":
                    return redirect()->route('director.home');
                    break;
                case "PURCHASING":
                    return redirect()->route('purchasing.home');
                    break;
                case "PRODUCTION":
                    return redirect()->route('production.home');
                    break;
                case "HRD":
                    return redirect()->route('hrd.home');
                    break;
                case "BUSINESS":
                    return redirect()->route('business.home');
                    break;
                case "PE":
                    return redirect()->route('pe.home');
                    break;
                case "PLASTIC INJECTION":
                    return redirect()->route('pi.home');
                    break;
                case "COMPUTER":
                    return redirect()->route('computer.home');
                    break;
                case "MAINTENANCE":
                    return redirect()->route('maintenance.home');
                    break;
                case "PPIC":
                    return redirect()->route('ppic.home');
                    break;
            }
        } else {
            return view('welcome');
        }
    }
}
