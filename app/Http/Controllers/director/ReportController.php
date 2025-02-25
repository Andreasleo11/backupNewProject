<?php

namespace App\Http\Controllers\director;

use App\DataTables\DirectorQaqcReportsDataTable;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use PhpParser\Node\Stmt\Foreach_;

class ReportController extends Controller
{
    public function index(DirectorQaqcReportsDataTable $dataTable)
    {
        return $dataTable->render('director.qaqc.index');
    }

    public function detail($id)
    {
        $report = Report::with('details')->find($id);
        $files = File::where('doc_id', $report->doc_num)->get();
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
        return view('director.qaqc.detail', compact('report','user','autographNames', 'files'));
    }

    public function approve($id)
    {
        Report::find($id)->update([
            'is_approve' => true,
            'description' => null,
            'approved_at' => now()
        ]);
        return redirect()->route('director.qaqc.index')->with('success', 'Report approved!');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'description' => 'required'
        ]);

        $report = Report::findOrFail($id);

        $updateData = [
            'autograph_1' => null,
            'autograph_2' => null,
            'autograph_3' => null,
            'autograph_user_1' => null,
            'autograph_user_2' => null,
            'autograph_user_3' => null,
            'is_approve' => false,
            'description' => $request->description,
        ];

        if (!$report->first_reject) {
            $updateData['first_reject'] = true;
            $updateData['rejected_at'] = now();
        }

        $report->update($updateData);

        return redirect()->route('director.qaqc.index')->with('success', 'Report rejected!');
    }

    public function approveSelected(Request $request){
        $ids = $request->input('ids', []);

        if(empty($ids)) {
            return response()->json(['message' => 'No records selected for approval. (server)']);
        }

        try {
            foreach ($ids as $id) {
                Report::find($id)->update([
                    'is_approve' => true,
                    'description' => null,
                    'approved_at' => now()
                ]);
            }
            return response()->json(['message'=>'selected records approved successfully. (server)']);
        } catch (\Throwable $th) {
            return response()->json(['message'=>'failed to approve selected records. (server)']);
            throw $th;
        }
    }

    public function rejectSelected(Request $request){
        $ids = $request->input('ids', []);
        $rejectionReason = $request->input('rejection_reason');

        if(empty($ids)) {
            return response()->json(['message' => 'No records selected for rejection. (server)']);
        }

        try {
            foreach ($ids as $id) {
                $report = Report::findOrFail($id);

                $updateData = [
                    'autograph_1' => null,
                    'autograph_2' => null,
                    'autograph_3' => null,
                    'autograph_user_1' => null,
                    'autograph_user_2' => null,
                    'autograph_user_3' => null,
                    'is_approve' => false,
                    'description' => $rejectionReason,
                ];

                if (!$report->first_reject) {
                    $updateData['first_reject'] = true;
                    $updateData['rejected_at'] = now();
                }

                $report->update($updateData);
            }
            return response()->json(['message'=>'selected records rejected successfully. (server)']);
        } catch (\Throwable $th) {
            return response()->json(['message'=>'failed to reject selected records. (server)']);
            throw $th;
        }
    }

    // public function updateAll()
    // {
    //     $reports = Report::where('is_approve', -1)->get();
    //     // dd($reports);
    //     foreach ($reports as $report) {
    //         $report->update(['is_approve' => 2]);
    //     }
    // }
}
