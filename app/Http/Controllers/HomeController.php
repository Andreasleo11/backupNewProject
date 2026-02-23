<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(\App\Application\PurchaseRequest\Queries\GetPurchaseRequestStats $statsQuery)
    {
        $user = auth()->user();

        // If the user is a director or top-level management, show the advanced dashboard
        if ($user->hasRole(['director', 'pr-director', 'general-manager-jakarta', 'general-manager-karawang', 'head-management'])) {
            $prStats = $statsQuery->execute();

            return view('director.dashboard', compact('prStats'));
        }

        return view('home');
    }
}
