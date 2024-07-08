<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterStock;

class MasterTintaController extends Controller
{
    public function index()
    {
        $datas = MasterStock::with('stocktype')->get();
        dd($datas);
        return view('index stock tinta ?');
    }

    public function transactiontintaview()
    {
        return view('index untuk transaction tinta');
    }

    public function storetransaction()
    {
        //function untuk store in / out stock tinta 
    }
}
