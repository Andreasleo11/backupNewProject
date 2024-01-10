<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Detail;

class ReportHeaderController extends Controller
{
    public function create()
    {
        return view('reports.create-header');
    }

    public function store(Request $request)
    {
            $data = $request->all();
            

            // Validate the request data as needed
            // dd($data);

            // Extract common attributes
            $commonAttributes = [
                'Rec_Date' => $data['Rec_Date'],
                'Verify_Date' => $data['Verify_Date'],
                'Customer' => $data['Customer'],
                'Invoice_No' => $data['Invoice_No'],
            ];

            // Create the VerificationReportHeader and get its doc_num

            $report = Report::create($commonAttributes);
            

            // Save the main data to the database, including defect details
            foreach ($data['part_names'] as $key => $partName) {
                $customerDefectDetails = $data['customer_defect_detail'][$key] ?? [];
                $customerRemarks = $data['customer_Remark'][$key] ?? [];
            
                
                $daijoDefectDetails = $data['daijo_defect_detail'][$key] ?? [];
                $daijoRemarks = $data['daijo_Remark'][$key] ?? [];
                $attributes = [
                    'Report_Id' => $report->id,
                    'Part_Name' => $partName,
                    'Rec_Quantity' => $data['rec_quantity'][$key],
                    'Verify_Quantity' => $data['verify_quantity'][$key],
                    'Prod_Date' => $data['prod_date'][$key],
                    'Shift' => $data['shift'][$key],
                    'Can_Use' => $data['can_use'][$key],
                    'Customer_Defect' => $data['customer_defect'][$key],
                    'Daijo_Defect' => $data['daijo_defect'][$key],
                    // Extract defect details and remarks
                    // Assign values to attributes
                    'Customer_Defect_Detail' => json_encode($customerDefectDetails),
                    'Remark_Customer' => json_encode($customerRemarks),
                    'Daijo_Defect_Detail' => json_encode($daijoDefectDetails),
                    'Remark_Daijo' => json_encode($daijoRemarks),
                ];
            
            Detail::create($attributes);
            }
            
        
        return redirect()->back()->with('success', 'Verification report has been stored successfully.');
    }
}
