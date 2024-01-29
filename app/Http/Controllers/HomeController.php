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
            $department = $user->department;
            dd($department);
            switch ($department) {
                case "QA":
                    return redirect()->route('qaqc.home');
                    break;
                case "QC":
                    return redirect()->route('qaqc.home');
                    break;
                case "DIREKTUR":
                    return redirect()->route('director.home');
                    break;
            }
        }
        else {
            return redirect()->route('user.home');
        }
    }
}
