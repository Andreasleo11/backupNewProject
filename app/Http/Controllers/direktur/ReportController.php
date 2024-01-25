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

    public function approve(Request $request, $id)
    {
        $report = Report::find($id);

        // dd($request->all(), $id);

        if ($request->has('approve')) {
            // Approve the report
            $report->update(['is_approve' => true]);
            return redirect()->back()->with('success', 'Report approved successfully.');
        }

        if ($request->has('reject')) {
            // Reject the report
            $request->validate([
                'description' => 'required',
            ]);

            $report->update([
                'is_approve' => false,
                'description' => $request->input('description'),
            ]);

            return redirect()->back()->with('success', 'Report rejected successfully.');
        }
    }

}
