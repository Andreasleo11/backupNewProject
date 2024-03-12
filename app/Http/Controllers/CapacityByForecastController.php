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
        $time = UtiDateList::get();

        return view("production.capacity_forecast.index", compact("data"));
    }

}
