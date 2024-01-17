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
        return view('qaqc.reports.reports',compact('reports'));
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
        return view('qaqc.reports.report_view_detail', compact('report'));
    }

    public function uploadAutograph(Request $request, $reportId, $section)
    {
        try {
            // Ambil file gambar dari request
            $file = $request->file('autograph');
            
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
            $report->update(["autograph_{$section}" => $path]);

            return response()->json(['message' => 'Autograph uploaded successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
