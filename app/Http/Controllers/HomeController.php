<?php

namespace App\Http\Controllers;

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
        // return view('admin.home');

        if ($user->hasRole('admin')) {
            return view('admin.home');
        } elseif ($user->hasRole('director')) {
            return redirect()->intended(route('director'));            
        } else {
            $department = $user->department->name;

            if ($department === 'QC' || $department === 'QA') {
                return redirect()->route('qaqc');
            } elseif ($department === 'PURCHASING') {
                return redirect()->route('purchasing');
            } elseif($department === 'PERSONALIA' && $user->is_head) {
                return redirect()->route('hrd');
            } elseif($department === 'PE') {
                return redirect()->route('pe');
            }
            return view('home');
        }
    }
}
