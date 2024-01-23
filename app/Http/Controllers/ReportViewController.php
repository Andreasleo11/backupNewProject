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
        return view('reports.report-view',compact('reports'));
    }

    public function indexjoni()
    {
        $reports = Report::get();
        
        // dd($reports);
        return view('reports.report-view-joni',compact('reports'));
    }


    public function detail($id)
    {
        
        $report = Report::with('details')->find($id);
        // $user = Auth::user();
        $user =  Auth::user();
        foreach($report->details as $pd){
                    $data1 = json_decode($pd->daijo_defect_detail);
                    $data2 = json_decode($pd->customer_defect_detail);
                    $data3 = json_decode($pd->remark);
                   
                    $pd->daijo_defect_detail = $data1;
                    $pd->customer_defect_detail = $data2;
                    $pd->remark = $data3;
                    
                }
        // dd($report);
        // dd($user);

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];
        return view('reports.report-view-detail-development', compact('report','user','autographNames'));
    }


    public function detailjoni($id)
    {
        
        $report = Report::with('details')->find($id);
        // $user = Auth::user();
        $user =  Auth::user();
        foreach($report->details as $pd){
                    $data1 = json_decode($pd->daijo_defect_detail);
                    $data2 = json_decode($pd->customer_defect_detail);
                    $data3 = json_decode($pd->remark);
                   
                    $pd->daijo_defect_detail = $data1;
                    $pd->customer_defect_detail = $data2;
                    $pd->remark = $data3;
                    
                }
        // dd($report);
        // dd($user);

        $autographNames = [
            'autograph_name_1' => $report->autograph_user_1 ?? null,
            'autograph_name_2' => $report->autograph_user_2 ?? null,
            'autograph_name_3' => $report->autograph_user_3 ?? null,
        ];
        return view('reports.report-view-detail-development-joni', compact('report','user','autographNames'));
    }


    public function approvaljoni(Request $request, $id)
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



    public function uploadAtt(Request $request){
        $request->validate([
            'attachment' => 'required|mimes:pdf,doc,docx,xlsx,xls|max:5120', // Adjust allowed file types and size
            'reportId' => 'required|exists:reports,id',
        ]);
    
        $reportId = $request->input('reportId');
    
        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
    
            // Generate a unique filename
            $filename = time() . '_' . $file->getClientOriginalName();
    
            // Move the uploaded file to a storage location (you can customize the storage path)
            $file->storeAs('public/attachments', $filename);
    
            // Update the reports table with the attachment filename
            Report::where('id', $reportId)->update(['attachment' => $filename]);
    
            return redirect()->back()->with('success', 'Attachment uploaded and saved successfully.');
        }
    
        return redirect()->back()->with('error', 'Failed to upload and save attachment.');
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
        

public function editview($id)
{
    $report = Report::find($id);
    $details = $report->details;

    return view('reports.report-view-edit', compact('report','details'));
}

}
