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

        Report::where('id', $reportId)->update([
            'is_approve' => null,
            'description' => null,
        ]);
    
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


public function updateedit(Request $request, $id)
{
    $data = $request->all();

    foreach ($data['remark'] as $key => &$values) {
        $modifiedValues = [];
    
        $index = 0;
        while ($index < count($values)) {
            // Check if the value is "other"
            if ($values[$index] === 'other') {
                // Check if there is a next index and a next value
                if (isset($values[$index + 1])) {
                    // Replace "other" with the value from the next index
                    $modifiedValues[] = $values[$index + 1];
                    // Skip the next value
                    $index += 2;
                }
            } else {
                // Keep non-"other" values
                $modifiedValues[] = $values[$index];
                $index++;
            }
        }
    
        // Update the original array with the modified values
        $data['remark'][$key] = $modifiedValues;
    }
    
    // Remove the reference to $values
    unset($values);


    if (isset($data['customer_defect_detail'])) {
        $data['customer_defect_detail'] = array_map(function ($array) {
            $foundNull = false;
            return array_filter($array, function ($value) use (&$foundNull) {
                if ($value === null && !$foundNull) {
                    $foundNull = true;
                    return false;
                }
                return true;
            });
        }, $data['customer_defect_detail']);
    }
    
    // Remove only the first null value from daijo_defect_detail
    if (isset($data['daijo_defect_detail'])) {
        $data['daijo_defect_detail'] = array_map(function ($array) {
            $foundNull = false;
            return array_filter($array, function ($value) use (&$foundNull) {
                if ($value === null && !$foundNull) {
                    $foundNull = true;
                    return false;
                }
                return true;
            });
        }, $data['daijo_defect_detail']);
    }

    // dd($data);  

      // Update the existing record
      $report = Report::findOrFail($id);

      // Update common attributes
      $report->update([
          'Rec_Date' => $data['rec_Date'],
          'Verify_Date' => $data['verify_date'],
          'Customer' => $data['customer'],
          'Invoice_No' => $data['invoice_no'],
          'num_of_parts' => $data['num_of_parts'],
          // Add other attributes as needed
      ]);
  
      // Update details
      foreach ($data['part_names'] as $key => $partName) {
          $customerDefectDetails = $data['customer_defect_detail'][$key] ?? [];  
          $daijoDefectDetails = $data['daijo_defect_detail'][$key] ?? [];
          $Remarks = $data['remark'][$key] ?? [];
  
          $detail = Detail::where('Report_Id', $id)->where('Part_Name', $partName)->first();
  
          // Update detail attributes
          $detail->update([
              'Part_Name' => $partName,
              'Rec_Quantity' => $data['rec_quantity'][$key],
              'Verify_Quantity' => $data['verify_quantity'][$key],
              'Prod_Date' => $data['prod_date'][$key],
              'Shift' => $data['shift'][$key],
              'Can_Use' => $data['can_use'][$key],
              'Cant_use' => $data['cant_use'][$key],
              // Extract defect details and remarks
              // Add other attributes as needed
              'Customer_Defect_Detail' => json_encode($customerDefectDetails),
              'Daijo_Defect_Detail' => json_encode($daijoDefectDetails),
              'Remark' => json_encode($Remarks),
          ]);
      }
  
      // Redirect to a view or route after the update
      return redirect()->route('report.view')
          ->with('success', 'Data updated successfully');
    
    

}

}
