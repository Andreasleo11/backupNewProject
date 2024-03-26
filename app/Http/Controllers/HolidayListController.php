<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UtiHolidayList;

class HolidayListController extends Controller
{
    public function index()
    {
        $datas = UtiHolidayList::get();
        // dd($datas);
        return view("setting.holidayindex", compact("datas"));
    }

    public function create()
    {
        return view("setting.createholiday");
    }

    public function store(Request $request)
    {

         // Validate the request data
         $validatedData = $request->validate([
            'date' => 'required|date',
            'holiday_name' => 'required|string',
            'description' => 'required|string',
            'halfday' => 'required|boolean',
        ]);
        // Convert boolean value to integer (0 or 1)
        $halfday = $validatedData['halfday'] ? 1 : 0;

        // Create a new Holiday instance and fill it with the validated data
        $holiday = new UtiHolidayList();
        $holiday->date = $validatedData['date'];
        $holiday->holiday_name = $validatedData['holiday_name'];
        $holiday->description = $validatedData['description'];
        $holiday->half_day = $halfday; // Assign the converted value

        // Save the holiday to the database
        $holiday->save();


        // Redirect the user back or to another page
        return redirect()->route('indexholiday')->with('success', 'Holiday created successfully!');
    }
}
