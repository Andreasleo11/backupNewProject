<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\delsched_delfilter;
use App\Models\delsched_delsum;
use App\Models\delsched_final;
use App\Models\delsched_finalwip;
use App\Models\delsched_solist;
use App\Models\delsched_stock;
use App\Models\delsched_stockwip;

use App\Models\DelschedFinal;
use App\Models\DelschedFinalWip;
use App\DataTables\DeliveryNewTableDataTable;
use App\DataTables\WipFinalDsDataTable;

class DeliveryScheduleController extends Controller
{
    public function index(DeliveryNewTableDataTable $dataTable)
    {
        // $datas = DelschedFinal::paginate(10);
       
        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render("business.dsnewindex");
    }

    public function indexfinal(WipFinalDsDataTable $dataTable)
    {
        // $datas = DelschedFinalWip::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }
      
        return $dataTable->render("business.dsnewindexwip");
    }
}
