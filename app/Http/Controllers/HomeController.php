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
            $abbrString = $this->abbreviateString($department);
            // dd($abbrString);
            return redirect()->route($abbrString . '.home');
        } else {
            return view('welcome');
        }
    }

    private function abbreviateString($string) {
        // Check if the string contains multiple words
        if (strpos($string, ' ') !== false) {
            // Convert the string to lowercase
            $lowercaseString = strtolower($string);

            // Split the string into words
            $words = explode(' ', $lowercaseString);

            // Initialize an empty abbreviation string
            $abbreviation = '';

            // Iterate through each word
            foreach ($words as $word) {
                // Add the first letter of each word to the abbreviation string
                $abbreviation .= substr($word, 0, 1);
            }

            // Return the abbreviation
            return $abbreviation;
        } else {
            // Return the lowercase version of the string
            return strtolower($string);
        }
    }
}
