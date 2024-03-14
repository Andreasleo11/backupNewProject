<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CapLineSummary;
use App\Models\UtiDateList;


class CapacityByForecastController extends Controller
{
    public function index()
    {
		
        $data = CapLineSummary::get();
        $time = UtiDateList::find(8);
        $startdate = $time->start_date;        

        return view("production.capacity_forecast.index", compact( "data","startdate"));
    }


    public function viewstep1()
    {
        return view("production.capacity_forecast.step1");
    }

    public function step1()
    {
        //algoritma step 1 


        return view("production.capacity_forecast.step2");
    }

    public function step2()
    {
        //algoritma step 2 


        return view("production.capacity_forecast.step3");
    }

    public function step3()
    {
        //algoritma step 3



        //return balik ke view index
        return redirect()->route('capacityforecastindex');
    }

}
