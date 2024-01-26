<?php

namespace App\Http\Controllers\direktur;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::get();
        return view('direktur.qaqc.index', compact('reports'));
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
        return view('direktur.qaqc.detail', compact('report','user','autographNames'));
    }

    public function approve($id)
    {
        Report::where('id', $id)->update([
            'is_approve' => true,
            'description' => null,
        ]);
        Report::find($id)->update(['is_approve' => true]);
        return redirect()->route('direktur.qaqc.index')->with('success', 'Document approved!');
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

        return redirect()->route('direktur.qaqc.index')->with('success', 'Document rejected!');
    }

}
