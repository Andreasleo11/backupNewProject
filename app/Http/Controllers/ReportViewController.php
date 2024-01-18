<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Detail;
use App\Models\User;

class ReportViewController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $reports = Report::get();

        // dd($reports);
        return view('qaqc.reports.report_view',compact('reports'));
    }

    public function detail($id)
    {
        $report = Report::with('details')->find($id);
        // $user = Auth::user();
        $user =  Auth::user();
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
        return view('qaqc.reports.report-view-detail-development',compact('report'));
        // dd($user);

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];
        return view('qaqc.reports.report-view-detail-development', compact('report','user','autographNames'));
    }

    public function uploadAutograph(Request $request, $reportId, $section)
{
    try {
        // Ambil file gambar dari request
        $file = $request->file('autograph');
        $user = Auth::user()->name;

        // Validate file upload
        $request->validate([
            'autograph' => 'required|image|mimes:png,jpg,jpeg',
        ]);

            $directory = public_path('autographs');

            // Simpan gambar ke penyimpanan atau database sesuai kebutuhan
            // Misalnya, simpan ke penyimpanan dengan nama file yang unik
            $path = 'tandatangan_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            $file->move($directory, $path);

        // Update kolom autograph di database
        $report = Report::find($reportId);
        $report->update([
            "autograph_{$section}" => $path
        ]);
        $report->update([
            "autograph_user_{$section}" => $user
        ]);

        return response()->json(['message' => 'Autograph uploaded successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function saveImagePath(Request $request, $reportId, $section)
{
    $username = Auth::check() ? Auth::user()->name : '';
    $imagePath = $username . '.png';

    // Save $imagePath to the database for the specified $reportId and $section
    $report = Report::find($reportId);
        $report->update([
            "autograph_{$section}" => $imagePath
        ]);
        $report->update([
            "autograph_user_{$section}" => $username
        ]);

    return response()->json(['message' => 'Image path saved successfully']);
}


}
