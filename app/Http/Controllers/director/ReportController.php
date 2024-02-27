<?php

namespace App\Http\Controllers\director;

use App\DataTables\ReportsDataTable;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(ReportsDataTable $dataTable)
    {
        $reports = Report::whereNotNull('autograph_1')
            ->whereNotNull('autograph_2')
            ->whereNotNull('autograph_3')
            ->get();

        // return view('director.qaqc.index', compact('reports'));
        return $dataTable->render('director.qaqc.index');
    }

    public function detail($id)
    {
        $report = Report::with('details')->find($id);
        $user =  Auth::user();
        foreach($report->details as $pd){
                    $data1 = json_decode($pd->daijo_defect_detail);
                    $data2 = json_decode($pd->customer_defect_detail);
                    $data3 = json_decode($pd->remark);

                    $pd->daijo_defect_detail = $data1;
                    $pd->customer_defect_detail = $data2;
                    $pd->remark = $data3;

        }

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];
        return view('director.qaqc.detail', compact('report','user','autographNames'));
    }

    public function approve($id)
    {
        Report::where('id', $id)->update([
            'is_approve' => true,
            'description' => null,
        ]);
        Report::find($id)->update(['is_approve' => true]);
        return redirect()->route('director.qaqc.index')->with('success', 'Report approved!');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'description' => 'required'
        ]);

        Report::find($id)->update([
            'is_approve' => false,
            'description' => $request->description
        ]);

        return redirect()->route('director.qaqc.index')->with('success', 'Report rejected!');
    }

}
