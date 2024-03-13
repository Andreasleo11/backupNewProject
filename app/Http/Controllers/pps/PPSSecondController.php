<?php

namespace App\Http\Controllers\pps;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PPSSecondController extends Controller
{
    public function indexscenario()
    {
        return view("pps.secondindex");
    }


    public function deliverysecond()
    {
        return view("pps.seconddelivery");
    }

    public function itemsecond()
    {
        return view("pps.seconditem");
    }

    public function linesecond()
    {
        return view("pps.secondline");
    }

    public function finalresultsecond()
    {
        return view("pps.finalresultppssecond");
    }
}
