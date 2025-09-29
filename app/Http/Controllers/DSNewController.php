<?php

namespace App\Http\Controllers;

use App\DataTables\DeliveryNewTableDataTable;
use App\DataTables\WipFinalDsDataTable;
use App\Models\DelschedFinal;
use App\Models\DelschedFinalWip;

class DSNewController extends Controller
{
    public function index(DeliveryNewTableDataTable $dataTable)
    {
        // $datas = DelschedFinal::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render('business.dsnewindex');
    }

    public function indexfinal(WipFinalDsDataTable $dataTable)
    {
        // $datas = DelschedFinalWip::paginate(10);

        // foreach($datas as $data)
        // {
        //     dd($data);
        // }

        return $dataTable->render('business.dsnewindexwip');
    }
}
