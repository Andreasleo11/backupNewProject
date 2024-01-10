<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Detail;

class ReportViewController extends Controller
{
    public function index()
    {
        $reports = Report::get();
        // dd($reports);
        return view('reports.report-view',compact('reports'));
    }


    public function detail($id)
    {
        
        $report = Report::with('details')->find($id);
      
        foreach($report->details as $pd){
                    $data1 = json_decode($pd->daijo_defect_detail);
                    $data2 = json_decode($pd->customer_defect_detail);
                    $data3 = json_decode($pd->remark_customer);
                    $data4 = json_decode($pd->remark_daijo);
                    $pd->daijo_defect_detail = $data1;
                    $pd->customer_defect_detail = $data2;
                    $pd->remark_customer = $data3;
                    $pd->remark_daijo = $data4;
                    
                }
        
        // dd($report);
        return view('reports.report-view-detail', compact('report'));
    }

       
}
