<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DelschedFinal;
use App\Models\DelschedFinalWip;

class DSNewController extends Controller
{
    public function index()
    {
        $datas = DelschedFinal::paginate(10);
       
        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return view("business.dsnewindex", compact("datas"));
    }

    public function indexfinal()
    {
        $datas = DelschedFinalWip::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }



        return view("business.dsnewindexwip", compact("datas"));
    }
}
