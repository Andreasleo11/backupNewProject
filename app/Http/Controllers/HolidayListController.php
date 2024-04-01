<?php

namespace App\Http\Controllers;

use App\Exports\HolidayListTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\HolidayListTemplateImport;
use Illuminate\Http\Request;
use App\Models\UtiHolidayList;
use Maatwebsite\Excel\Facades\Excel;

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

    public function downloadTemplate(){
        return Excel::download(new HolidayListTemplateExport(), 'holiday_list_template.xlsx');
    }

    public function uploadTemplate(Request $request){
        $request->validate([
            'holiday_file' => 'required|mimes:xlsx,xls', // Ensure the file is an Excel file
        ]);

        $file = $request->file('holiday_file');

        // // Parse the Excel file and get the data
        // $data = Excel::toArray(new HolidayListTemplateImport(), $file);

        Excel::import(new HolidayListTemplateImport(), $file);

        // Process and insert the data into the holiday list table
        return redirect()->back()->with('success', 'Holiday list template uploaded successfully.');
    }

    public function delete($id){
        UtiHolidayList::find($id)->delete();

        return redirect()->back()->with(['success' => 'Holiday has been deleted successfully!']);
    }

    public function update($id, Request $request){
        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string',
            'description' => 'required|string',
            'half_day' => 'required|boolean',
        ]);

        UtiHolidayList::find($id)->update([
            'date' => $validated['date'],
            'holiday_name' => $validated['name'],
            'description'=>$validated['description'],
            'half_day' => $validated['half_day']
        ]);

        return redirect()->back()->with(['success' => 'Holiday has been edited successfully!']);

    }
}
